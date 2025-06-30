<?php

namespace WechatOpenPlatformBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\OfficialAccount;
use WechatOpenPlatformBundle\Entity\User;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testGetIdReturnsNull(): void
    {
        $this->assertNull($this->user->getId());
    }

    public function testOpenIdGetterAndSetter(): void
    {
        $openId = 'oABCD1234567890';
        $this->user->setOpenId($openId);
        $this->assertSame($openId, $this->user->getOpenId());
    }

    public function testUnionIdGetterAndSetter(): void
    {
        $this->assertNull($this->user->getUnionId());
        
        $unionId = 'uABCD1234567890';
        $this->user->setUnionId($unionId);
        $this->assertSame($unionId, $this->user->getUnionId());
        
        $this->user->setUnionId(null);
        $this->assertNull($this->user->getUnionId());
    }

    public function testAccountGetterAndSetter(): void
    {
        $this->assertNull($this->user->getAccount());
        
        $account = $this->createMock(OfficialAccount::class);
        $this->user->setAccount($account);
        $this->assertSame($account, $this->user->getAccount());
        
        $this->user->setAccount(null);
        $this->assertNull($this->user->getAccount());
    }

    public function testLanguageGetterAndSetter(): void
    {
        $this->assertNull($this->user->getLanguage());
        
        $language = 'zh_CN';
        $this->user->setLanguage($language);
        $this->assertSame($language, $this->user->getLanguage());
        
        $this->user->setLanguage(null);
        $this->assertNull($this->user->getLanguage());
    }

    public function testSubscribeTimeGetterAndSetter(): void
    {
        $this->assertNull($this->user->getSubscribeTime());
        
        $subscribeTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $this->user->setSubscribeTime($subscribeTime);
        $this->assertSame($subscribeTime, $this->user->getSubscribeTime());
        
        $this->user->setSubscribeTime(null);
        $this->assertNull($this->user->getSubscribeTime());
    }

    public function testSubscribeSceneGetterAndSetter(): void
    {
        $this->assertNull($this->user->getSubscribeScene());
        
        $scene = 'ADD_SCENE_QR_CODE';
        $this->user->setSubscribeScene($scene);
        $this->assertSame($scene, $this->user->getSubscribeScene());
        
        $this->user->setSubscribeScene(null);
        $this->assertNull($this->user->getSubscribeScene());
    }

    public function testSubscribedGetterAndSetter(): void
    {
        $this->assertFalse($this->user->isSubscribed());
        
        $this->user->setSubscribed(true);
        $this->assertTrue($this->user->isSubscribed());
        
        $this->user->setSubscribed(false);
        $this->assertFalse($this->user->isSubscribed());
    }

    public function testGetAvatarUrlReturnsNull(): void
    {
        // TODO: 实现头像URL
        $this->assertNull($this->user->getAvatarUrl());
    }

    public function testGetOfficialAccountReturnsNull(): void
    {
        // TODO: 账号需要实现 OfficialAccountInterface
        $this->assertNull($this->user->getOfficialAccount());
    }

    public function testToString(): void
    {
        $this->assertSame('', (string) $this->user);
        
        $openId = 'oABCD1234567890';
        $this->user->setOpenId($openId);
        $this->assertSame($openId, (string) $this->user);
    }
}