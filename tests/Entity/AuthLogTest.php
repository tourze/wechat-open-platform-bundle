<?php

namespace WechatOpenPlatformBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\AuthLog;

class AuthLogTest extends TestCase
{
    private AuthLog $authLog;

    protected function setUp(): void
    {
        $this->authLog = new AuthLog();
    }

    public function testGetIdReturnsNull(): void
    {
        $this->assertNull($this->authLog->getId());
    }

    public function testTypeGetterAndSetter(): void
    {
        $type = 'authorized';
        $this->authLog->setType($type);
        $this->assertSame($type, $this->authLog->getType());
    }

    public function testOpenIdGetterAndSetter(): void
    {
        $openId = 'oABCD1234567890';
        $this->authLog->setOpenId($openId);
        $this->assertSame($openId, $this->authLog->getOpenId());
    }

    public function testRawDataGetterAndSetter(): void
    {
        $rawData = '{"component_appid":"wx123","authorizer_appid":"wx456"}';
        $this->authLog->setRawData($rawData);
        $this->assertSame($rawData, $this->authLog->getRawData());
    }

    public function testToString(): void
    {
        $this->assertSame('AuthLog #new', (string) $this->authLog);
        
        // 使用反射设置ID来测试非null ID的情况
        $reflection = new \ReflectionClass($this->authLog);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->authLog, 123);
        
        $this->assertSame('AuthLog #123', (string) $this->authLog);
    }
}