<?php

namespace WechatOpenPlatformBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\AuthCode;

class AuthCodeTest extends TestCase
{
    private AuthCode $authCode;

    protected function setUp(): void
    {
        $this->authCode = new AuthCode();
    }

    public function testGetIdReturnsZeroByDefault(): void
    {
        $this->assertSame(0, $this->authCode->getId());
    }

    public function testAccountGetterAndSetter(): void
    {
        $this->assertNull($this->authCode->getAccount());
        
        $account = $this->createMock(Account::class);
        $this->authCode->setAccount($account);
        $this->assertSame($account, $this->authCode->getAccount());
        
        $this->authCode->setAccount(null);
        $this->assertNull($this->authCode->getAccount());
    }

    public function testAuthCodeGetterAndSetter(): void
    {
        $this->assertNull($this->authCode->getAuthCode());
        
        $code = 'auth_code_123456';
        $this->authCode->setAuthCode($code);
        $this->assertSame($code, $this->authCode->getAuthCode());
    }

    public function testResultGetterAndSetter(): void
    {
        $this->assertNull($this->authCode->getResult());
        
        $result = [
            'access_token' => 'token123',
            'expires_in' => 7200,
            'refresh_token' => 'refresh123'
        ];
        $this->authCode->setResult($result);
        $this->assertSame($result, $this->authCode->getResult());
        
        $this->authCode->setResult(null);
        $this->assertNull($this->authCode->getResult());
    }

    public function testCreatedFromIpGetterAndSetter(): void
    {
        $this->assertNull($this->authCode->getCreatedFromIp());
        
        $ip = '192.168.1.1';
        $this->authCode->setCreatedFromIp($ip);
        $this->assertSame($ip, $this->authCode->getCreatedFromIp());
        
        $this->authCode->setCreatedFromIp(null);
        $this->assertNull($this->authCode->getCreatedFromIp());
    }

    public function testToString(): void
    {
        $this->assertSame('AuthCode #0', (string) $this->authCode);
        
        // 使用反射设置ID来测试非零ID的情况
        $reflection = new \ReflectionClass($this->authCode);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->authCode, 123);
        
        $this->assertSame('AuthCode #123', (string) $this->authCode);
    }

    public function testToStringWithNullId(): void
    {
        // 使用反射设置ID为null
        $reflection = new \ReflectionClass($this->authCode);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->authCode, null);
        
        $this->assertSame('AuthCode #new', (string) $this->authCode);
    }
}