<?php

namespace WechatOpenPlatformBundle\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Request\WithAccountRequest;

class WithAccountRequestTest extends TestCase
{
    private WithAccountRequest $request;

    protected function setUp(): void
    {
        // 创建一个测试用的具体实现类，因为 WithAccountRequest 是抽象类
        $this->request = new class extends WithAccountRequest {
            protected string $method = 'GET';
            protected string $endpoint = '/test';
            
            public function getRequestPath(): string
            {
                return $this->endpoint;
            }
            
            public function getRequestOptions(): array
            {
                return [];
            }
        };
    }

    public function testAccountGetterAndSetter(): void
    {
        $account = new Account();
        $account->setAppId('test-app-id');
        
        $this->request->setAccount($account);
        
        $this->assertSame($account, $this->request->getAccount());
        $this->assertSame('test-app-id', $this->request->getAccount()->getAppId());
    }

    public function testIsAbstractClass(): void
    {
        $reflection = new \ReflectionClass(WithAccountRequest::class);
        $this->assertTrue($reflection->isAbstract());
    }

    public function testExtendsApiRequest(): void
    {
        $this->assertInstanceOf(\HttpClientBundle\Request\ApiRequest::class, $this->request);
    }
}