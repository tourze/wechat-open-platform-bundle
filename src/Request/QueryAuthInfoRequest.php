<?php

namespace WechatOpenPlatformBundle\Request;

/**
 * 使用授权码获取授权信息
 *
 * 当用户在第三方平台授权页中完成授权流程后，第三方平台开发者可以在回调 URI 中通过 URL 参数获取授权码。
 * 使用以下接口可以换取公众号/小程序的授权信息。
 * 建议保存授权信息中的刷新令牌（authorizer_refresh_token）。使用过程中如遇到问题，可在开放平台服务商专区发帖交流。
 *
 * 注意： 公众号/小程序可以自定义选择部分权限授权给第三方平台，因此第三方平台开发者需要通过该接口来获取公众号/小程序具体授权了哪些权限，而不是简单地认为自己声明的权限就是公众号/小程序授权的权限。
 *
 * @see https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/token/authorization_info.html
 */
class QueryAuthInfoRequest extends WithAccountRequest
{
    /**
     * @var string 授权码, 会在授权成功时返回给第三方平台，详见第三方平台授权流程说明
     */
    private string $authorizationCode;

    public function getRequestPath(): string
    {
        return '/cgi-bin/component/api_query_auth';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'json' => [
                'component_appid' => $this->getAccount()->getAppId(),
                'authorization_code' => $this->getAuthorizationCode(),
            ],
        ];
    }

    public function getAuthorizationCode(): string
    {
        return $this->authorizationCode;
    }

    public function setAuthorizationCode(string $authorizationCode): void
    {
        $this->authorizationCode = $authorizationCode;
    }
}
