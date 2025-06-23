<?php

namespace WechatOpenPlatformBundle\Request\OAuth2;

use HttpClientBundle\Request\ApiRequest;
use WechatOpenPlatformBundle\Entity\OfficialAccount as Account;

/**
 * OAuth2 协议中通过 code 换取 access_token
 *
 * @see https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/Before_Develop/Official_Accounts/official_account_website_authorization.html
 */
class GetAccessTokenRequest extends ApiRequest
{
    /**
     * @var Account 公众号
     */
    private Account $account;

    /**
     * @var string 填写第一步获取的code参数
     */
    private string $code;

    private string $grantType = 'authorization_code';

    private \WechatOpenPlatformBundle\Entity\Account $openPlatformAccount;

    public function getRequestPath(): string
    {
        return '/sns/oauth2/component/access_token';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'json' => [
                'appid' => $this->getAccount()->getAppId(),
                'code' => $this->getCode(),
                'grant_type' => $this->getGrantType(),
                'component_appid' => $this->getOpenPlatformAccount()->getAppId(),
                'component_access_token' => $this->getOpenPlatformAccount()->getComponentAccessToken(),
            ],
        ];
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }

    public function setGrantType(string $grantType): void
    {
        $this->grantType = $grantType;
    }

    public function getOpenPlatformAccount(): \WechatOpenPlatformBundle\Entity\Account
    {
        return $this->openPlatformAccount;
    }

    public function setOpenPlatformAccount(\WechatOpenPlatformBundle\Entity\Account $openPlatformAccount): void
    {
        $this->openPlatformAccount = $openPlatformAccount;
    }
}
