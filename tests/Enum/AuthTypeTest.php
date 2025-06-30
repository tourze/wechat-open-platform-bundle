<?php

namespace WechatOpenPlatformBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Enum\AuthType;

class AuthTypeTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertEquals('base', AuthType::BASE->value);
        $this->assertEquals('user_info', AuthType::USER_INFO->value);
    }

    public function testGetLabel(): void
    {
        $this->assertEquals('基础授权', AuthType::BASE->getLabel());
        $this->assertEquals('用户信息授权', AuthType::USER_INFO->getLabel());
    }

    public function testFromValue(): void
    {
        $this->assertEquals(AuthType::BASE, AuthType::from('base'));
        $this->assertEquals(AuthType::USER_INFO, AuthType::from('user_info'));
    }

    public function testTryFromValue(): void
    {
        $this->assertEquals(AuthType::BASE, AuthType::tryFrom('base'));
        $this->assertEquals(AuthType::USER_INFO, AuthType::tryFrom('user_info'));
        $this->assertNull(AuthType::tryFrom('invalid'));
    }

    public function testCases(): void
    {
        $cases = AuthType::cases();
        $this->assertCount(2, $cases);
        $this->assertContains(AuthType::BASE, $cases);
        $this->assertContains(AuthType::USER_INFO, $cases);
    }

    public function testItemableInterface(): void
    {
        $item = AuthType::BASE->toSelectItem();
        $this->assertEquals('基础授权', $item['label']);
        $this->assertEquals('基础授权', $item['text']);
        $this->assertEquals('base', $item['value']);
        $this->assertEquals('基础授权', $item['name']);
        
        $array = AuthType::BASE->toArray();
        $this->assertEquals('base', $array['value']);
        $this->assertEquals('基础授权', $array['label']);
    }

    public function testSelectableInterface(): void
    {
        $options = AuthType::genOptions();
        $this->assertCount(2, $options);
        
        // 验证选项格式
        foreach ($options as $option) {
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('value', $option);
        }
    }
}