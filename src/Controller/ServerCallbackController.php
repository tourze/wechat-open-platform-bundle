<?php

namespace WechatOpenPlatformBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatHelper\XML;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\ServerMessage;
use WechatOpenPlatformBundle\Event\WechatOpenPlatformServerMessageResponseEvent;
use WechatOpenPlatformBundle\Repository\AuthorizerRepository;
use WechatOpenPlatformBundle\Service\AuthorizerService;

/**
 * 服务端回调（代开发）
 *
 * @see https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/token/authorize_event.html
 */
class ServerCallbackController extends AbstractController
{
    use EncryptTrait;

    public function __construct(
        private readonly AuthorizerService $authorizerService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly AuthorizerRepository $authorizerRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route(path: '/wechat-open-platform/server/{appId}/{authAppId}', name: 'wechat-open-platform-server-index')]
    public function __invoke(
        Account $account,
        string $authAppId,
        Request $request,
    ): Response {
        $this->logger->info('服务端回调收到服务端请求', [
            'content' => $request->getContent(),
            'query' => $request->query->all(),
        ]);
        if ($request->query->get('signature') !== $this->signature([
            $account->getToken(),
            $request->query->get('timestamp'),
            $request->query->get('nonce'),
        ])) {
            throw new BadRequestException('Invalid request signature.', 400);
        }

        $message = $this->parseMessage($request->getContent());
        if (!is_array($message) || empty($message)) {
            throw new BadRequestException('No message received.');
        }

        if (!empty($message['Encrypt'])) {
            try {
                $message = $this->decryptMessage($message, $request, $account);
                if (json_validate($message)) {
                    $message = json_decode($message, true);
                } else {
                    $message = XML::parse($message);
                }
            } catch (\Throwable $exception) {
                $this->logger->error('解密数据报错', [
                    'exception' => $exception,
                ]);

                return new Response('failed');
            }
        }

        // 读取回调的公众号
        $authorizer = $this->authorizerRepository->findOneBy(['appId' => $authAppId]);
        $this->authorizerService->transformToOfficialAccount($authorizer);

        $msg = new ServerMessage();
        $msg->setAccount($account);
        $msg->setMessage($message);
        $msg->setAuthorizer($authorizer);

        $event = new WechatOpenPlatformServerMessageResponseEvent();
        $event->setMessage($msg);
        $this->eventDispatcher->dispatch($event);

        $this->entityManager->persist($msg);
        $this->entityManager->flush();
        
        $response = $msg->getResponse();
        $content = is_array($response) && !empty($response) ? XML::build($response) : 'success';

        return new Response($content);
    }

    private function signature(array $params): string
    {
        sort($params, SORT_STRING);

        return sha1(implode($params));
    }
}