<?php

namespace WechatOpenPlatformBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Request\GetComponentAccessTokenRequest;

class GetComponentAccessTokenRequestTest extends TestCase
{
    private GetComponentAccessTokenRequest $request;
    
    protected function setUp(): void
    {
        $this->request = new GetComponentAccessTokenRequest();
    }
    
    public function testGetRequestPath(): void
    {
        $this->assertSame('/cgi-bin/component/api_component_token', $this->request->getRequestPath());
    }
    
    public function testGetRequestMethod(): void
    {
        // 默认返回 null，由 HTTP 客户端决定具体的请求方法
        $this->assertNull($this->request->getRequestMethod());
    }
    
    public function testGetRequestOptions(): void
    {
        $componentAppId = 'wx123456789';
        $componentAppSecret = 'test_secret';
        $componentVerifyTicket = 'test_ticket';
        
        $this->request->setComponentAppId($componentAppId);
        $this->request->setComponentAppSecret($componentAppSecret);
        $this->request->setComponentVerifyTicket($componentVerifyTicket);
        
        $expected = [
            'json' => [
                'component_appid' => $componentAppId,
                'component_appsecret' => $componentAppSecret,
                'component_verify_ticket' => $componentVerifyTicket,
            ],
        ];
        
        $this->assertEquals($expected, $this->request->getRequestOptions());
    }
    
    public function testSetAndGetComponentAppId(): void
    {
        $componentAppId = 'wx123456789';
        
        $this->request->setComponentAppId($componentAppId);
        
        $this->assertSame($componentAppId, $this->request->getComponentAppId());
    }
    
    public function testSetAndGetComponentAppSecret(): void
    {
        $componentAppSecret = 'test_secret';
        
        $this->request->setComponentAppSecret($componentAppSecret);
        
        $this->assertSame($componentAppSecret, $this->request->getComponentAppSecret());
    }
    
    public function testSetAndGetComponentVerifyTicket(): void
    {
        $componentVerifyTicket = 'test_ticket';
        
        $this->request->setComponentVerifyTicket($componentVerifyTicket);
        
        $this->assertSame($componentVerifyTicket, $this->request->getComponentVerifyTicket());
    }
    
    public function testRequestWithUninitialized(): void
    {
        // 在PHP 8中，访问未初始化的属性会抛出异常
        $this->expectException(\Error::class);
        $this->request->getRequestOptions();
    }
}