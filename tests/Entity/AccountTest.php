<?php

namespace WechatOpenPlatformBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\Authorizer;

class AccountTest extends TestCase
{
    private Account $account;

    protected function setUp(): void
    {
        $this->account = new Account();
    }

    public function testGetIdReturnsNull(): void
    {
        $this->assertNull($this->account->getId());
    }

    public function testAppIdGetterAndSetter(): void
    {
        $appId = 'wx1234567890';
        $this->account->setAppId($appId);
        $this->assertSame($appId, $this->account->getAppId());
    }

    public function testAppSecretGetterAndSetter(): void
    {
        $appSecret = 'secret123456789';
        $this->account->setAppSecret($appSecret);
        $this->assertSame($appSecret, $this->account->getAppSecret());
    }

    public function testTokenGetterAndSetter(): void
    {
        $this->assertNull($this->account->getToken());
        
        $token = 'token123';
        $this->account->setToken($token);
        $this->assertSame($token, $this->account->getToken());
        
        $this->account->setToken(null);
        $this->assertNull($this->account->getToken());
    }

    public function testAesKeyGetterAndSetter(): void
    {
        $this->assertNull($this->account->getAesKey());
        
        $aesKey = 'aeskey123456789';
        $this->account->setAesKey($aesKey);
        $this->assertSame($aesKey, $this->account->getAesKey());
    }

    public function testComponentVerifyTicketGetterAndSetter(): void
    {
        $this->assertNull($this->account->getComponentVerifyTicket());
        
        $ticket = 'ticket123';
        $this->account->setComponentVerifyTicket($ticket);
        $this->assertSame($ticket, $this->account->getComponentVerifyTicket());
    }

    public function testComponentAccessTokenGetterAndSetter(): void
    {
        $this->assertNull($this->account->getComponentAccessToken());
        
        $token = 'component_token123';
        $this->account->setComponentAccessToken($token);
        $this->assertSame($token, $this->account->getComponentAccessToken());
    }

    public function testComponentAccessTokenExpireTimeGetterAndSetter(): void
    {
        $this->assertNull($this->account->getComponentAccessTokenExpireTime());
        
        $expireTime = new \DateTimeImmutable('2024-12-31 23:59:59');
        $this->account->setComponentAccessTokenExpireTime($expireTime);
        $this->assertSame($expireTime, $this->account->getComponentAccessTokenExpireTime());
    }

    public function testComponentAppIdGetterAndSetter(): void
    {
        $this->assertNull($this->account->getComponentAppId());
        
        $componentAppId = 'wx_component_123';
        $this->account->setComponentAppId($componentAppId);
        $this->assertSame($componentAppId, $this->account->getComponentAppId());
    }

    public function testAuthorizersCollection(): void
    {
        $this->assertCount(0, $this->account->getAuthorizers());
        
        $authorizer = $this->createMock(Authorizer::class);
        $authorizer->expects($this->once())
            ->method('setAccount')
            ->with($this->account);
        
        $this->account->addAuthorizer($authorizer);
        $this->assertCount(1, $this->account->getAuthorizers());
        $this->assertTrue($this->account->getAuthorizers()->contains($authorizer));
        
        // 测试重复添加
        $this->account->addAuthorizer($authorizer);
        $this->assertCount(1, $this->account->getAuthorizers());
    }

    public function testRemoveAuthorizer(): void
    {
        $authorizer = $this->createMock(Authorizer::class);
        $authorizer->expects($this->exactly(2))
            ->method('setAccount')
            ->willReturnCallback(function ($account) use ($authorizer) {
                static $callCount = 0;
                $callCount++;
                if ($callCount === 1) {
                    $this->assertSame($this->account, $account);
                } else {
                    $this->assertNull($account);
                }
                return $authorizer;
            });
        $authorizer->expects($this->once())
            ->method('getAccount')
            ->willReturn($this->account);
        
        $this->account->addAuthorizer($authorizer);
        $this->assertCount(1, $this->account->getAuthorizers());
        
        $this->account->removeAuthorizer($authorizer);
        $this->assertCount(0, $this->account->getAuthorizers());
    }

    public function testRetrievePlainArray(): void
    {
        $appId = 'wx1234567890';
        $this->account->setAppId($appId);
        
        $plainArray = $this->account->retrievePlainArray();
        
        $this->assertArrayHasKey('id', $plainArray);
        $this->assertArrayHasKey('appId', $plainArray);
        $this->assertNull($plainArray['id']);
        $this->assertSame($appId, $plainArray['appId']);
    }

    public function testAccessTokenMethods(): void
    {
        $token = 'component_access_token_123';
        $this->account->setComponentAccessToken($token);
        
        $this->assertSame($token, $this->account->getAccessToken());
        $this->assertSame('component_access_token', $this->account->getAccessTokenKeyName());
    }

    public function testToString(): void
    {
        $this->assertSame('', (string) $this->account);
    }
}