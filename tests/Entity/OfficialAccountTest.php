<?php

namespace WechatOpenPlatformBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\OfficialAccount;

class OfficialAccountTest extends TestCase
{
    private OfficialAccount $officialAccount;

    protected function setUp(): void
    {
        $this->officialAccount = new OfficialAccount();
    }

    public function testGetIdReturnsNull(): void
    {
        $this->assertNull($this->officialAccount->getId());
    }

    public function testAppIdGetterAndSetter(): void
    {
        $appId = 'wx1234567890';
        $this->officialAccount->setAppId($appId);
        $this->assertSame($appId, $this->officialAccount->getAppId());
    }

    public function testComponentAppIdGetterAndSetter(): void
    {
        $this->assertNull($this->officialAccount->getComponentAppId());
        
        $componentAppId = 'wx_component_123';
        $this->officialAccount->setComponentAppId($componentAppId);
        $this->assertSame($componentAppId, $this->officialAccount->getComponentAppId());
        
        $this->officialAccount->setComponentAppId(null);
        $this->assertNull($this->officialAccount->getComponentAppId());
    }

    public function testToString(): void
    {
        $this->assertSame('', (string) $this->officialAccount);
        
        $appId = 'wx1234567890';
        $this->officialAccount->setAppId($appId);
        $this->assertSame($appId, (string) $this->officialAccount);
    }
}