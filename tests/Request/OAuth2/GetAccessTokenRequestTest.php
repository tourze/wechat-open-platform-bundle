<?php

namespace WechatOpenPlatformBundle\Tests\Request\OAuth2;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\OfficialAccount;
use WechatOpenPlatformBundle\Request\OAuth2\GetAccessTokenRequest;

class GetAccessTokenRequestTest extends TestCase
{
    private GetAccessTokenRequest $request;
    private OfficialAccount $account;
    private Account $openPlatformAccount;
    
    protected function setUp(): void
    {
        $this->request = new GetAccessTokenRequest();
        $this->account = $this->createMock(OfficialAccount::class);
        $this->openPlatformAccount = $this->createMock(Account::class);
    }
    
    public function testGetRequestPath(): void
    {
        $this->assertSame('/sns/oauth2/component/access_token', $this->request->getRequestPath());
    }
    
    public function testGetRequestMethod(): void
    {
        // 默认返回 null，由 HTTP 客户端决定具体的请求方法
        $this->assertNull($this->request->getRequestMethod());
    }
    
    public function testGetRequestOptions(): void
    {
        $appId = 'wx123456789';
        $code = 'test_code';
        $grantType = 'authorization_code';
        $componentAppId = 'wx_component_123';
        $componentAccessToken = 'test_component_token';
        
        $this->account->expects($this->once())
            ->method('getAppId')
            ->willReturn($appId);
        
        $this->openPlatformAccount->expects($this->once())
            ->method('getAppId')
            ->willReturn($componentAppId);
        
        $this->openPlatformAccount->expects($this->once())
            ->method('getComponentAccessToken')
            ->willReturn($componentAccessToken);
        
        $this->request->setAccount($this->account);
        $this->request->setCode($code);
        $this->request->setOpenPlatformAccount($this->openPlatformAccount);
        
        $expected = [
            'json' => [
                'appid' => $appId,
                'code' => $code,
                'grant_type' => $grantType,
                'component_appid' => $componentAppId,
                'component_access_token' => $componentAccessToken,
            ],
        ];
        
        $this->assertEquals($expected, $this->request->getRequestOptions());
    }
    
    public function testSetAndGetAccount(): void
    {
        $this->request->setAccount($this->account);
        
        $this->assertSame($this->account, $this->request->getAccount());
    }
    
    public function testSetAndGetCode(): void
    {
        $code = 'test_code';
        
        $this->request->setCode($code);
        
        $this->assertSame($code, $this->request->getCode());
    }
    
    public function testSetAndGetGrantType(): void
    {
        $grantType = 'test_grant_type';
        
        $this->request->setGrantType($grantType);
        
        $this->assertSame($grantType, $this->request->getGrantType());
    }
    
    public function testDefaultGrantType(): void
    {
        // 验证默认值
        $this->assertSame('authorization_code', $this->request->getGrantType());
    }
    
    public function testSetAndGetOpenPlatformAccount(): void
    {
        $this->request->setOpenPlatformAccount($this->openPlatformAccount);
        
        $this->assertSame($this->openPlatformAccount, $this->request->getOpenPlatformAccount());
    }
    
    public function testRequestWithUninitialized(): void
    {
        // 在PHP 8中，访问未初始化的属性会抛出异常
        $this->expectException(\Error::class);
        $this->request->getAccount();
    }
}