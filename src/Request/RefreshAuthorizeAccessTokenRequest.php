<?php

namespace WechatOpenPlatformBundle\Request;

/**
 * 在公众号/小程序接口调用令牌（authorizer_access_token）失效时，可以使用刷新令牌（authorizer_refresh_token）获取新的接口调用令牌。
 * 使用过程中如遇到问题，可在开放平台服务商专区发帖交流。
 *
 * 注意： authorizer_access_token 有效期为 2 小时，开发者需要缓存 authorizer_access_token，避免获取/刷新接口调用令牌的 API 调用触发每日限额。
 * 缓存方法可以参考：https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_access_token.html
 *
 * @see https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/token/api_authorizer_token.html
 */
class RefreshAuthorizeAccessTokenRequest extends WithAccountRequest
{
    /**
     * @var string 授权方 appid
     */
    private string $authorizerAppId;

    /**
     * @var string 刷新令牌，获取授权信息时得到
     */
    private string $authorizerRefreshToken;

    public function getRequestPath(): string
    {
        return '/cgi-bin/component/api_authorizer_token';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'json' => [
                'component_appid' => $this->getAccount()->getAppId(),
                'authorizer_appid' => $this->getAuthorizerAppId(),
                'authorizer_refresh_token' => $this->getAuthorizerRefreshToken(),
            ],
        ];
    }

    public function getAuthorizerAppId(): string
    {
        return $this->authorizerAppId;
    }

    public function setAuthorizerAppId(string $authorizerAppId): void
    {
        $this->authorizerAppId = $authorizerAppId;
    }

    public function getAuthorizerRefreshToken(): string
    {
        return $this->authorizerRefreshToken;
    }

    public function setAuthorizerRefreshToken(string $authorizerRefreshToken): void
    {
        $this->authorizerRefreshToken = $authorizerRefreshToken;
    }
}
