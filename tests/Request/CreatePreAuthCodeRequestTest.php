<?php

namespace WechatOpenPlatformBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Request\CreatePreAuthCodeRequest;

class CreatePreAuthCodeRequestTest extends TestCase
{
    private CreatePreAuthCodeRequest $request;
    private Account $account;
    
    protected function setUp(): void
    {
        $this->request = new CreatePreAuthCodeRequest();
        $this->account = $this->createMock(Account::class);
    }
    
    public function testGetRequestPath(): void
    {
        $this->assertSame('/cgi-bin/component/api_create_preauthcode', $this->request->getRequestPath());
    }
    
    public function testGetRequestMethod(): void
    {
        // 默认返回 null，由 HTTP 客户端决定具体的请求方法
        $this->assertNull($this->request->getRequestMethod());
    }
    
    public function testGetRequestOptions(): void
    {
        $appId = 'wx123456789';
        
        $this->account->expects($this->once())
            ->method('getAppId')
            ->willReturn($appId);
        
        $this->request->setAccount($this->account);
        
        $expected = [
            'json' => [
                'component_appid' => $appId,
            ],
        ];
        
        $this->assertEquals($expected, $this->request->getRequestOptions());
    }
    
    public function testSetAndGetAccount(): void
    {
        $this->request->setAccount($this->account);
        
        $this->assertSame($this->account, $this->request->getAccount());
    }
    
    public function testRequestWithUninitialized(): void
    {
        // 在PHP 8中，访问未初始化的属性会抛出异常
        $this->expectException(\Error::class);
        $this->request->getAccount();
    }
}