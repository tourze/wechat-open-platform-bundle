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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\ServerMessage;
use WechatOpenPlatformBundle\Event\WechatOpenPlatformServerMessageResponseEvent;
use WechatOpenPlatformBundle\Helper\XML;
use WechatOpenPlatformBundle\Repository\ServerMessageRepository;
use WechatOpenPlatformBundle\Request\CreatePreAuthCodeRequest;
use WechatOpenPlatformBundle\Service\ApiService;
use WechatOpenPlatformBundle\Service\AuthorizerService;
use WeuiBundle\Service\NoticeService;
use Yiisoft\Arrays\ArrayHelper;

#[Route('/wechat-open-platform')]
class AuthController extends AbstractController
{
    use EncryptTrait;

    public function __construct(
        private readonly NoticeService $noticeService,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * 授权入口页
     */
    #[Route('/auth/{appId}')]
    public function index(
        Account $account,
        Request $request,
        ApiService $apiService,
    ): Response {
        $options = [
            'auth_type' => 3, // 1 表示手机端仅展示公众号；2 表示仅展示小程序，3 表示公众号和小程序都展示。如果为未指定，则默认小程序和公众号都展示。
        ];
        if ($request->query->has('biz_appid')) {
            // 指定AppID
            $options['biz_appid'] = $request->query->get('biz_appid');
        }

        $callbackUrl = $this->generateUrl('wechat-open-platform-auth-callback', [
            'appId' => $account->getAppId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $codeRequest = new CreatePreAuthCodeRequest();
        $codeRequest->setAccount($account);
        $codeResponse = $apiService->request($codeRequest);
        $options['pre_auth_code'] = ArrayHelper::getValue($codeResponse, 'pre_auth_code');

        $queries = \array_merge(
            $options,
            [
                'component_appid' => $account->getAppId(),
                'redirect_uri' => $callbackUrl,
            ]
        );

        return $this->render('@WechatOpenPlatform/redirect.html.twig', [
            'url' => 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?' . http_build_query($queries),
        ]);
    }

    /**
     * 授权回调
     */
    #[Route('/auth-callback/{appId}', name: 'wechat-open-platform-auth-callback')]
    public function callback(
        Account $account,
        Request $request,
        LoggerInterface $logger,
        AuthorizerService $authorizerService,
        ServerMessageRepository $messageRepository,
        EventDispatcherInterface $eventDispatcher,
    ): Response {
        $logger->info('授权回调收到服务端请求', [
            'content' => $request->getContent(),
            'query' => $request->query->all(),
        ]);
        if ($request->query->has('auth_code')) {
            $authorizer = $authorizerService->createOrUpdateAuthorizer($account, $request->query->get('auth_code'));
            if (!$authorizer) {
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
                $logger->error('解密数据报错', [
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
        $eventDispatcher->dispatch($event);

        $content = $msg->getResponse() ? XML::build($msg->getResponse()) : 'success';

        return new Response($content);
    }
}
