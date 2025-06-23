<?php

namespace WechatOpenPlatformBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatHelper\XML;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\ServerMessage;
use WechatOpenPlatformBundle\Event\WechatOpenPlatformServerMessageResponseEvent;
use WechatOpenPlatformBundle\Service\AuthorizerService;
use WeuiBundle\Service\NoticeService;

/**
 * 授权回调
 */
class AuthCallbackController extends AbstractController
{
    use EncryptTrait;

    public function __construct(
        private readonly NoticeService $noticeService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly AuthorizerService $authorizerService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route('/wechat-open-platform/auth-callback/{appId}', name: 'wechat-open-platform-auth-callback')]
    public function __invoke(
        Account $account,
        Request $request,
    ): Response {
        $this->logger->info('授权回调收到服务端请求', [
            'content' => $request->getContent(),
            'query' => $request->query->all(),
        ]);
        if ($request->query->has('auth_code')) {
            $authorizer = $this->authorizerService->createOrUpdateAuthorizer($account, $request->query->get('auth_code'));
            if ($authorizer === null) {
                throw new HttpException(302, '找不到授权信息');
            }

            return $this->noticeService->weuiSuccess('授权成功');
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

        $msg = new ServerMessage();
        $msg->setAccount($account);
        $msg->setMessage($message);
        $this->entityManager->persist($msg);
        $this->entityManager->flush();

        $event = new WechatOpenPlatformServerMessageResponseEvent();
        $event->setMessage($msg);
        $this->eventDispatcher->dispatch($event);

        $response = $msg->getResponse();
        $content = is_array($response) && !empty($response) ? XML::build($response) : 'success';

        return new Response($content);
    }
}