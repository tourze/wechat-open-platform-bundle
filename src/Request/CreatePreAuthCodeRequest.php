<?php

namespace WechatOpenPlatformBundle\Request;

/**
 * 预授权码（pre_auth_code）是第三方平台方实现授权托管的必备信息，每个预授权码有效期为 1800秒。
 * 需要先获取令牌才能调用。使用过程中如遇到问题，可在开放平台服务商专区发帖交流。
 *
 * @see https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/token/pre_auth_code.html
 */
class CreatePreAuthCodeRequest extends WithAccountRequest
{
    public function getRequestPath(): string
    {
        return '/cgi-bin/component/api_create_preauthcode';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'json' => [
                'component_appid' => $this->getAccount()->getAppId(),
            ],
        ];
    }
}
