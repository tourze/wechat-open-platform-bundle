<?php

namespace WechatOpenPlatformBundle\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Client\ApiClient;
use HttpClientBundle\Client\ClientTrait;
use HttpClientBundle\Exception\HttpClientException;
use HttpClientBundle\Request\RequestInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use WechatOpenPlatformBundle\Request\GetComponentAccessTokenRequest;
use WechatOpenPlatformBundle\Request\WithAccountRequest;
use Yiisoft\Json\Json;

class ApiService extends ApiClient
{
    use ClientTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getBaseUrl(): string
    {
        return 'https://api.weixin.qq.com';
    }

    protected function getRequestMethod(RequestInterface $request): string
    {
        return $request->getRequestMethod() ?: 'POST';
    }

    protected function getRequestOptions(RequestInterface $request): ?array
    {
        $options = $request->getRequestOptions();

        // 自动加上token
        if ($request instanceof WithAccountRequest) {
            $account = $request->getAccount();
            if ($account->getComponentAccessToken() === null || CarbonImmutable::now()->greaterThanOrEqualTo($account->getComponentAccessTokenExpireTime())) {
                // 刷新Token
                $tokenRequest = new GetComponentAccessTokenRequest();
                $tokenRequest->setComponentAppId($account->getAppId());
                $tokenRequest->setComponentAppSecret($account->getAppSecret());
                $tokenRequest->setComponentVerifyTicket($account->getComponentVerifyTicket());
                $tokenResponse = $this->request($tokenRequest);

                $account->setComponentAccessToken($tokenResponse['component_access_token']);
                // 这里的时间，我们减少了60s
                $account->setComponentAccessTokenExpireTime(CarbonImmutable::now()->addSeconds($tokenResponse['expires_in'] - 60));
                $this->entityManager->persist($account);
                $this->entityManager->flush();
            }
            $options['query']['component_access_token'] = $account->getComponentAccessToken();
        }

        return $options;
    }

    protected function formatResponse(RequestInterface $request, ResponseInterface $response): mixed
    {
        $json = $response->getContent();
        $json = Json::decode($json);

        if (isset($json['errcode'])) {
            if (0 !== $json['errcode']) {
                throw new HttpClientException($request, $response, $json['errmsg'], $json['errcode']);
            }
        }

        return $json;
    }
}
