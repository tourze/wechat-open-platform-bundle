<?php

namespace WechatOpenPlatformBundle\Controller;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Nzo\UrlEncryptorBundle\Encryptor\Encryptor;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Entity\AuthLog;
use WechatOfficialAccountBundle\Entity\User;
use WechatOfficialAccountBundle\Enum\AuthType;
use WechatOfficialAccountBundle\Enum\Language;
use WechatOfficialAccountBundle\Repository\AuthLogRepository;
use WechatOfficialAccountBundle\Repository\UserRepository;
use WechatOfficialAccountBundle\Request\Jssdk\GetJsapiTicketRequest;
use WechatOfficialAccountBundle\Request\User\GetUserBasicInfoRequest;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOpenPlatformBundle\Event\OAuthGetBaseUserInfoEvent;
use WechatOpenPlatformBundle\Repository\AccountRepository;
use WechatOpenPlatformBundle\Request\OAuth2\GetAccessTokenRequest;
use WeuiBundle\Service\NoticeService;
use Yiisoft\Json\Json;

#[Route('/wechat-open-platform')]
class OAuth2Controller extends AbstractController
{
    public function __construct(
        private readonly NoticeService $noticeService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 获取JSSDK配置
     *
     * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html
     */
    #[Route(path: '/jssdk/{appId}', name: 'wechat_open_platform_jssdk', methods: ['GET', 'POST'])]
    public function jssdk(Account $account, Request $request, OfficialAccountClient $client): Response
    {
        $ticketRequest = new GetJsapiTicketRequest();
        $ticketRequest->setAccount($account);
        $re = $client->request($ticketRequest);

        $jsApiList = ['updateAppMessageShareData', 'updateTimelineShareData'];
        if ($api = $request->query->get('api')) {
            $jsApiList = explode(',', (string) $api);
        }

        $timestamp = time();
        $nonce = md5($timestamp);
        $url = $request->query->has('url', '');
        $signature = sha1(sprintf('jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s', $re['ticket'], $nonce, $timestamp, $url));
        $config = [
            'debug' => $request->query->get('debug', false),
            'beta' => $request->query->get('beta', false),
            'jsApiList' => $jsApiList,
            'openTagList' => ['wx-open-launch-app'],
            'appId' => $account->getAppId(),
            'nonceStr' => $nonce,
            'timestamp' => $timestamp,
            'url' => $url,
            'signature' => $signature,
        ];

        return $this->json($config);
    }

    /**
     * 获取简单的用户信息（openid）
     */
    #[Route(path: '/base-user/{appId}', name: 'wechat_open_platform_base_user', methods: ['GET'])]
    public function oauthBaseInfo(
        Account $account,
        Request $request,
        OfficialAccountClient $client,
        UserRepository $userRepository,
        AuthLogRepository $authLogRepository,
        EventDispatcherInterface $eventDispatcher,
        AccountRepository $componentAccountRepository,
        LoggerInterface $logger,
        Encryptor $encryptor,
    ): Response {
        if (!$account->getComponentAppId()) {
            throw new NotFoundHttpException('该公众号不支持开放平台授权');
        }
        $componentAccount = $componentAccountRepository->findOneBy([
            'appId' => $account->getComponentAppId(),
        ]);
        if (!$componentAccount) {
            throw new NotFoundHttpException('找不到开放平台信息');
        }

        // 如果有带code，说明跳转回来了
        if ($request->query->has('code')) {
            $r = new GetAccessTokenRequest();
            $r->setAccount($account);
            $r->setOpenPlatformAccount($componentAccount);
            $r->setCode($request->query->get('code'));
            $user = $client->request($r);
            $logger->info('开放平台获得微信公众号简单用户信息', [
                'account' => $account,
                'user' => $user,
            ]);

            $authLog = new AuthLog();
            $authLog->setType(AuthType::BASE);
            $authLog->setOpenId($user['openid']);
            $authLog->setRawData(Json::encode($user));
            $this->entityManager->persist($authLog);
            $this->entityManager->flush();

            // 本地保存一份啦
            $local = $userRepository->findOneBy(['openId' => $user['openid']]);
            if (!$local) {
                // 当 scope 为 snsapi_base 时 $oauth->userFromCode($code); 对象里只有 id，没有其它信息。
                $local = new User();
                $local->setAccount($account);
                $local->setOpenId($user['openid']);
                $local->setLanguage(Language::zh_CN);
            }
            if (isset($user['unionid'])) {
                $local->setUnionId($user['unionid']);
            }
            $this->entityManager->persist($local);
            $this->entityManager->flush();

            // 尝试同步其他信息
            $r = new GetUserBasicInfoRequest();
            $r->setOpenid($local->getOpenId());
            $r->setAccount($componentAccount);
            $user = $client->request($r);
            if (isset($user['subscribe_time'])) {
                $local->setSubscribeTime(Carbon::createFromTimestamp($user['subscribe_time'], date_default_timezone_get()));
            }
            if (isset($user['subscribe_scene'])) {
                $local->setSubscribeScene($user['subscribe_scene']);
            }
            if (isset($user['subscribe'])) {
                $local->setSubscribed((bool) $user['subscribe']);
            }
            if (isset($user['unionid'])) {
                $local->setUnionId($user['unionid']);
            }
            $this->entityManager->persist($local);
            $this->entityManager->flush();

            // 分发事件
            $e = new OAuthGetBaseUserInfoEvent();
            $e->setUser($local);
            $e->setAccount($account);
            $eventDispatcher->dispatch($e);
            if ($e->getResponse()) {
                return $e->getResponse();
            }

            if ($request->query->has('callbackUrl')) {
                $url = $request->query->get('callbackUrl');
                if (str_contains($url, '{{ encryptOpenId }}')) {
                    $url = str_replace('{{ encryptOpenId }}', $encryptor->encrypt($local->getOpenId()), $url);
                }

                return $this->redirect($url);
            }

            return $this->noticeService->weuiSuccess('授权成功', '请返回继续');
        }

        // 跳转
        $redirectUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query([
            'appid' => $account->getAppId(),
            'redirect_uri' => $request->getUri(),
            'response_type' => 'code',
            'scope' => 'snsapi_base',
            'state' => uniqid(),
            'component_appid' => $account->getComponentAppId(),
        ]);

        return $this->redirect($redirectUrl);
    }
}
