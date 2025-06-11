<?php

namespace WechatOpenPlatformBundle\Service;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\DoctrineAsyncInsertBundle\Service\AsyncInsertService;
use WechatOfficialAccountBundle\Entity\Account as OfficialAccount;
use WechatOfficialAccountBundle\Repository\AccountRepository as OfficialAccountRepository;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\AuthCode;
use WechatOpenPlatformBundle\Entity\Authorizer;
use WechatOpenPlatformBundle\Repository\AuthorizerRepository;
use WechatOpenPlatformBundle\Request\QueryAuthInfoRequest;

class AuthorizerService
{
    public function __construct(
        private readonly ApiService $apiService,
        private readonly AuthorizerRepository $authorizerRepository,
        private readonly LoggerInterface $logger,
        private readonly AsyncInsertService $doctrineService,
        private readonly OfficialAccountRepository $officialAccountRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createOrUpdateAuthorizer(Account $account, string $authCode): ?Authorizer
    {
        $result = new AuthCode();
        $result->setAccount($account);
        $result->setAuthCode($authCode);

        try {
            // 使用授权码获取授权信息
            $codeRequest = new QueryAuthInfoRequest();
            $codeRequest->setAccount($account);
            $codeRequest->setAuthorizationCode($authCode);
            $res = $this->apiService->request($codeRequest);

            $result->setResult($res);
            $this->doctrineService->asyncInsert($result);

            if (!isset($res['authorization_info'])) {
                return null;
            }
        } catch (\Throwable $exception) {
            $this->logger->error('使用授权码获取授权信息报错', [
                'account' => $account,
                'authCode' => $authCode,
                'exception' => $exception,
            ]);

            return null;
        }

        $authorizer = $this->authorizerRepository->findOneBy([
            'account' => $account,
            'appId' => $res['authorization_info']['authorizer_appid'],
        ]);
        if (!$authorizer) {
            $authorizer = new Authorizer();
            $authorizer->setAccount($account);
            $authorizer->setAppId($res['authorization_info']['authorizer_appid']);
        }

        $authorizer->setAuthorizerAccessToken($res['authorization_info']['authorizer_access_token'] ?? '');
        $authorizer->setAccessTokenExpireTime(Carbon::now()->addSeconds($res['authorization_info']['expires_in']));
        $authorizer->setAuthorizerRefreshToken($res['authorization_info']['authorizer_refresh_token'] ?? '');
        $authorizer->setFuncInfo($res['authorization_info']['func_info'] ?? null);
        $authorizer->setValid(true);
        $this->entityManager->persist($authorizer);
        $this->entityManager->flush();

        return $authorizer;
    }

    public function transformToOfficialAccount(Authorizer $authorizer): OfficialAccount
    {
        $account = $this->officialAccountRepository->findOneBy([
            'appId' => $authorizer->getAppId(),
        ]);
        if (!$account) {
            $account = new OfficialAccount();
            $account->setAppId($authorizer->getAppId());
            $account->setAppSecret('');
            $account->setName("{$authorizer->getAccount()->getAppId()}授权{$authorizer->getAppId()}");
        }
        $account->setComponentAppId($authorizer->getAccount()->getAppId());
        $account->setAccessToken($authorizer->getAccessToken());
        $account->setAccessTokenExpireTime($authorizer->getAccessTokenExpireTime());
        $account->setValid(true);
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return $account;
    }
}
