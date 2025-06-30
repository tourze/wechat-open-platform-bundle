<?php

namespace WechatOpenPlatformBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Request\QueryAuthInfoRequest;

class QueryAuthInfoRequestTest extends TestCase
{
    private QueryAuthInfoRequest $request;
    private Account $account;
    
    protected function setUp(): void
    {
        $this->request = new QueryAuthInfoRequest();
        $this->account = $this->createMock(Account::class);
    }
    
    public function testGetRequestPath(): void
    {
        $this->assertSame('/cgi-bin/component/api_query_auth', $this->request->getRequestPath());
    }
    
    public function testGetRequestMethod(): void
    {
        // 默认返回 null，由 HTTP 客户端决定具体的请求方法
        $this->assertNull($this->request->getRequestMethod());
    }
    
    public function testGetRequestOptions(): void
    {
        $appId = 'wx123456789';
        $authorizationCode = 'test_auth_code';
        
        $this->account->expects($this->once())
            ->method('getAppId')
            ->willReturn($appId);
        
        $this->request->setAccount($this->account);
        $this->request->setAuthorizationCode($authorizationCode);
        
        $expected = [
            'json' => [
                'component_appid' => $appId,
                'authorization_code' => $authorizationCode,
            ],
        ];
        
        $this->assertEquals($expected, $this->request->getRequestOptions());
    }
    
    public function testSetAndGetAccount(): void
    {
        $this->request->setAccount($this->account);
        
        $this->assertSame($this->account, $this->request->getAccount());
    }
    
    public function testSetAndGetAuthorizationCode(): void
    {
        $authorizationCode = 'test_auth_code';
        
        $this->request->setAuthorizationCode($authorizationCode);
        
        $this->assertSame($authorizationCode, $this->request->getAuthorizationCode());
    }
    
    public function testRequestWithUninitialized(): void
    {
        // 在PHP 8中，访问未初始化的属性会抛出异常
        $this->expectException(\Error::class);
        $this->request->getAccount();
    }
}