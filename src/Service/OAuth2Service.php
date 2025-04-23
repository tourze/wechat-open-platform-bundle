<?php

namespace WechatOpenPlatformBundle\Service;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\JsonRPC\Core\Exception\ApiException;
use WechatOfficialAccountBundle\Entity\AuthLog;
use WechatOfficialAccountBundle\Entity\User;
use WechatOfficialAccountBundle\Enum\AuthType;
use WechatOfficialAccountBundle\Enum\Language;
use WechatOfficialAccountBundle\Repository\UserRepository;
use WechatOfficialAccountBundle\Request\User\GetUserBasicInfoRequest;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOpenPlatformBundle\Event\OAuthGetBaseUserInfoEvent;
use WechatOpenPlatformBundle\Repository\AccountRepository;
use WechatOpenPlatformBundle\Request\OAuth2\GetAccessTokenRequest;
use Yiisoft\Json\Json;

class OAuth2Service
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly \WechatOfficialAccountBundle\Repository\AccountRepository $accountRepository,
        private readonly AccountRepository $componentAccountRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly OfficialAccountClient $client,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getUserInfoByCode(
        string $code,
        string $appId = '',
    ) {
        if ($appId) {
            $account = $this->accountRepository->findOneBy(['appId' => $appId]);
        } else {
            $account = $this->accountRepository->findOneBy([]);
        }
        if (!$account) {
            throw new ApiException('公众号不存在');
        }

        if (!$account->getComponentAppId()) {
            throw new NotFoundHttpException('该公众号不支持开放平台授权');
        }
        $componentAccount = $this->componentAccountRepository->findOneBy([
            'appId' => $account->getComponentAppId(),
        ]);
        if (!$componentAccount) {
            throw new NotFoundHttpException('找不到开放平台信息');
        }

        $r = new GetAccessTokenRequest();
        $r->setAccount($account);
        $r->setOpenPlatformAccount($componentAccount);
        $r->setCode($code);
        $user = $this->client->request($r);
        $this->logger->info('开放平台获得微信公众号简单用户信息', [
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
        $local = $this->userRepository->findOneBy(['openId' => $user['openid']]);
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
        $user = $this->client->request($r);
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
        $this->eventDispatcher->dispatch($e);
        if ($e->getResponse()) {
            return $e->getResponse();
        }

        return $user;
    }
}
