<?php

namespace WechatOpenPlatformBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nzo\UrlEncryptorBundle\Encryptor\Encryptor;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use WechatOpenPlatformBundle\Entity\AuthLog;
use WechatOpenPlatformBundle\Entity\OfficialAccount;
use WechatOpenPlatformBundle\Entity\User;
use WechatOpenPlatformBundle\Enum\AuthType;
use WechatOpenPlatformBundle\Enum\Language;
use WechatOpenPlatformBundle\Event\OAuthGetBaseUserInfoEvent;
use WechatOpenPlatformBundle\Repository\AccountRepository;
use WechatOpenPlatformBundle\Repository\UserRepository;
use WechatOpenPlatformBundle\Request\OAuth2\GetAccessTokenRequest;
use WeuiBundle\Service\NoticeService;
use Yiisoft\Json\Json;

// use WechatOfficialAccountBundle\Request\User\GetUserBasicInfoRequest; // TODO: 需要实现
// use WechatOfficialAccountBundle\Service\OfficialAccountClient; // TODO: 需要实现

/**
 * 获取简单的用户信息（openid）
 */
class OAuthBaseInfoController extends AbstractController
{
    public function __construct(
        private readonly NoticeService $noticeService,
        private readonly EntityManagerInterface $entityManager,
        // private readonly OfficialAccountClient $client, // TODO: 需要实现
        private readonly UserRepository $userRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AccountRepository $componentAccountRepository,
        private readonly LoggerInterface $logger,
        private readonly Encryptor $encryptor,
    ) {
    }

    #[Route(path: '/wechat-open-platform/base-user/{appId}', name: 'wechat_open_platform_base_user', methods: ['GET'])]
    public function __invoke(
        OfficialAccount $account,
        Request $request,
    ): Response {
        if ($account->getComponentAppId() === null) {
            throw new NotFoundHttpException('该公众号不支持开放平台授权');
        }
        $componentAccount = $this->componentAccountRepository->findOneBy([
            'appId' => $account->getComponentAppId(),
        ]);
        if ($componentAccount === null) {
            throw new NotFoundHttpException('找不到开放平台信息');
        }

        // 如果有带code，说明跳转回来了
        if ($request->query->has('code')) {
            $r = new GetAccessTokenRequest();
            $r->setAccount($account);
            $r->setOpenPlatformAccount($componentAccount);
            $r->setCode($request->query->get('code'));
            // TODO: 需要实现 OfficialAccountClient
            // $user = $this->client->request($r);
            $user = ['openid' => 'test_openid']; // 临时数据
            $this->logger->info('开放平台获得微信公众号简单用户信息', [
                'account' => $account,
                'user' => $user,
            ]);

            $authLog = new AuthLog();
            $authLog->setType(AuthType::BASE->value);
            $authLog->setOpenId($user['openid']);
            $authLog->setRawData(Json::encode($user));
            $this->entityManager->persist($authLog);
            $this->entityManager->flush();

            // 本地保存一份啦
            $local = $this->userRepository->findOneBy(['openId' => $user['openid']]);
            if ($local === null) {
                // 当 scope 为 snsapi_base 时 $oauth->userFromCode($code); 对象里只有 id，没有其它信息。
                $local = new User();
                $local->setAccount($account);
                $local->setOpenId($user['openid']);
                $local->setLanguage(Language::zh_CN->value);
            }
            // TODO: 实际实现时需要处理 unionid
            // if (isset($user['unionid'])) {
            //     $local->setUnionId($user['unionid']);
            // }
            $this->entityManager->persist($local);
            $this->entityManager->flush();

            // 尝试同步其他信息
            // TODO: 需要实现 GetUserBasicInfoRequest 和 OfficialAccountClient
            // $r = new GetUserBasicInfoRequest();
            // $r->setOpenid($local->getOpenId());
            // $r->setAccount($componentAccount);
            // $user = $this->client->request($r);
            // if (isset($user['subscribe_time'])) {
            //     $local->setSubscribeTime(CarbonImmutable::createFromTimestamp($user['subscribe_time'], date_default_timezone_get()));
            // }
            // if (isset($user['subscribe_scene'])) {
            //     $local->setSubscribeScene($user['subscribe_scene']);
            // }
            // if (isset($user['subscribe'])) {
            //     $local->setSubscribed((bool) $user['subscribe']);
            // }
            // if (isset($user['unionid'])) {
            //     $local->setUnionId($user['unionid']);
            // }
            $this->entityManager->persist($local);
            $this->entityManager->flush();

            // 分发事件
            $e = new OAuthGetBaseUserInfoEvent();
            $e->setUser($local);
            $e->setAccount($account);
            $this->eventDispatcher->dispatch($e);
            if ($e->getResponse() !== null) {
                return $e->getResponse();
            }

            if ($request->query->has('callbackUrl')) {
                $url = $request->query->get('callbackUrl');
                if (str_contains($url, '{{ encryptOpenId }}')) {
                    $url = str_replace('{{ encryptOpenId }}', $this->encryptor->encrypt($local->getOpenId()), $url);
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