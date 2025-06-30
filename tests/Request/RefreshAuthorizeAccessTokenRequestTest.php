<?php

namespace WechatOpenPlatformBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Request\RefreshAuthorizeAccessTokenRequest;

class RefreshAuthorizeAccessTokenRequestTest extends TestCase
{
    private RefreshAuthorizeAccessTokenRequest $request;
    private Account $account;
    
    protected function setUp(): void
    {
        $this->request = new RefreshAuthorizeAccessTokenRequest();
        $this->account = $this->createMock(Account::class);
    }
    
    public function testGetRequestPath(): void
    {
        $this->assertSame('/cgi-bin/component/api_authorizer_token', $this->request->getRequestPath());
    }
    
    public function testGetRequestMethod(): void
    {
        // 默认返回 null，由 HTTP 客户端决定具体的请求方法
        $this->assertNull($this->request->getRequestMethod());
    }
    
    public function testGetRequestOptions(): void
    {
        $componentAppId = 'wx123456789';
        $authorizerAppId = 'wx_auth_123';
        $authorizerRefreshToken = 'test_refresh_token';
        
        $this->account->expects($this->once())
            ->method('getAppId')
            ->willReturn($componentAppId);
        
        $this->request->setAccount($this->account);
        $this->request->setAuthorizerAppId($authorizerAppId);
        $this->request->setAuthorizerRefreshToken($authorizerRefreshToken);
        
        $expected = [
            'json' => [
                'component_appid' => $componentAppId,
                'authorizer_appid' => $authorizerAppId,
                'authorizer_refresh_token' => $authorizerRefreshToken,
            ],
        ];
        
        $this->assertEquals($expected, $this->request->getRequestOptions());
    }
    
    public function testSetAndGetAccount(): void
    {
        $this->request->setAccount($this->account);
        
        $this->assertSame($this->account, $this->request->getAccount());
    }
    
    public function testSetAndGetAuthorizerAppId(): void
    {
        $authorizerAppId = 'wx_auth_123';
        
        $this->request->setAuthorizerAppId($authorizerAppId);
        
        $this->assertSame($authorizerAppId, $this->request->getAuthorizerAppId());
    }
    
    public function testSetAndGetAuthorizerRefreshToken(): void
    {
        $authorizerRefreshToken = 'test_refresh_token';
        
        $this->request->setAuthorizerRefreshToken($authorizerRefreshToken);
        
        $this->assertSame($authorizerRefreshToken, $this->request->getAuthorizerRefreshToken());
    }
    
    public function testRequestWithUninitialized(): void
    {
        // 在PHP 8中，访问未初始化的属性会抛出异常
        $this->expectException(\Error::class);
        $this->request->getAccount();
    }
}