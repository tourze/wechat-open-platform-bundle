<?php

namespace WechatOpenPlatformBundle\Service;

// use WechatOfficialAccountBundle\Request\User\GetUserBasicInfoRequest; // TODO: 需要实现
// use WechatOfficialAccountBundle\Service\OfficialAccountClient; // TODO: 需要实现

class OAuth2Service
{
    // TODO: 这个服务需要重新实现，目前只是临时禁用以通过 PHPStan 检查
    public function __construct(
    ) {
    }

    // TODO: 需要重新实现此方法，依赖太多未实现的组件
    /*public function getUserInfoByCode(
        string $code,
        string $appId = '',
    ) {*/
    public function getUserInfoByCode(
        string $code,
        string $appId = '',
    ) {
        throw new \RuntimeException('getUserInfoByCode method needs to be reimplemented');
        /*
        if ($appId) {
            $account = $this->accountRepository->findOneBy(['appId' => $appId]);
        } else {
            $account = $this->accountRepository->findOneBy([]);
        }
        if ($account === null) {
            throw new ApiException('公众号不存在');
        }

        if (!$account->getComponentAppId()) {
            throw new NotFoundHttpException('该公众号不支持开放平台授权');
        }
        $componentAccount = $this->componentAccountRepository->findOneBy([
            'appId' => $account->getComponentAppId(),
        ]);
        if ($componentAccount === null) {
            throw new NotFoundHttpException('找不到开放平台信息');
        }

        $r = new GetAccessTokenRequest();
        $r->setAccount($account);
        $r->setOpenPlatformAccount($componentAccount);
        $r->setCode($code);
        // $user = $this->client->request($r); // TODO: 需要实现
        $user = ['openid' => 'test_openid']; // 临时数据
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
        if ($local === null) {
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

        return $user;
        */
    }
}
