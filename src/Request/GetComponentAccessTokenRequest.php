<?php

namespace WechatOpenPlatformBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 令牌（component_access_token）是第三方平台接口的调用凭据。令牌的获取是有限制的，每个令牌的有效期为 2 小时，请自行做好令牌的管理，在令牌快过期时（比如1小时50分），重新调用接口获取。
 * 如未特殊说明，令牌一般作为被调用接口的 GET 参数 component_access_token 的值使用
 *
 * @see https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/token/component_access_token.html
 */
class GetComponentAccessTokenRequest extends ApiRequest
{
    /**
     * @var string 第三方平台 appid
     */
    private string $componentAppId;

    /**
     * @var string 第三方平台 appsecret
     */
    private string $componentAppSecret;

    /**
     * @var string 微信后台推送的 ticket
     */
    private string $componentVerifyTicket;

    public function getRequestPath(): string
    {
        return '/cgi-bin/component/api_component_token';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'json' => [
                'component_appid' => $this->getComponentAppId(),
                'component_appsecret' => $this->getComponentAppSecret(),
                'component_verify_ticket' => $this->getComponentVerifyTicket(),
            ],
        ];
    }

    public function getComponentAppId(): string
    {
        return $this->componentAppId;
    }

    public function setComponentAppId(string $componentAppId): void
    {
        $this->componentAppId = $componentAppId;
    }

    public function getComponentAppSecret(): string
    {
        return $this->componentAppSecret;
    }

    public function setComponentAppSecret(string $componentAppSecret): void
    {
        $this->componentAppSecret = $componentAppSecret;
    }

    public function getComponentVerifyTicket(): string
    {
        return $this->componentVerifyTicket;
    }

    public function setComponentVerifyTicket(string $componentVerifyTicket): void
    {
        $this->componentVerifyTicket = $componentVerifyTicket;
    }
}
