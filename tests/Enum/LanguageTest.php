<?php

namespace WechatOpenPlatformBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Enum\Language;

class LanguageTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertEquals('zh_CN', Language::zh_CN->value);
        $this->assertEquals('zh_TW', Language::zh_TW->value);
        $this->assertEquals('en', Language::en->value);
    }

    public function testGetLabel(): void
    {
        $this->assertEquals('简体中文', Language::zh_CN->getLabel());
        $this->assertEquals('繁体中文', Language::zh_TW->getLabel());
        $this->assertEquals('英文', Language::en->getLabel());
    }

    public function testFromValue(): void
    {
        $this->assertEquals(Language::zh_CN, Language::from('zh_CN'));
        $this->assertEquals(Language::zh_TW, Language::from('zh_TW'));
        $this->assertEquals(Language::en, Language::from('en'));
    }

    public function testTryFromValue(): void
    {
        $this->assertEquals(Language::zh_CN, Language::tryFrom('zh_CN'));
        $this->assertEquals(Language::zh_TW, Language::tryFrom('zh_TW'));
        $this->assertEquals(Language::en, Language::tryFrom('en'));
        $this->assertNull(Language::tryFrom('invalid'));
    }

    public function testCases(): void
    {
        $cases = Language::cases();
        $this->assertCount(3, $cases);
        $this->assertContains(Language::zh_CN, $cases);
        $this->assertContains(Language::zh_TW, $cases);
        $this->assertContains(Language::en, $cases);
    }

    public function testItemableInterface(): void
    {
        $item = Language::zh_CN->toSelectItem();
        $this->assertEquals('简体中文', $item['label']);
        $this->assertEquals('简体中文', $item['text']);
        $this->assertEquals('zh_CN', $item['value']);
        $this->assertEquals('简体中文', $item['name']);
        
        $array = Language::zh_CN->toArray();
        $this->assertEquals('zh_CN', $array['value']);
        $this->assertEquals('简体中文', $array['label']);
    }

    public function testSelectableInterface(): void
    {
        $options = Language::genOptions();
        $this->assertCount(3, $options);
        
        // 验证选项格式
        foreach ($options as $option) {
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('value', $option);
        }
    }
}