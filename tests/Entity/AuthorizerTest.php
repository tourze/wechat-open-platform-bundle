<?php

namespace WechatOpenPlatformBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\Authorizer;
use WechatOpenPlatformBundle\Entity\ServerMessage;

class AuthorizerTest extends TestCase
{
    private Authorizer $authorizer;

    protected function setUp(): void
    {
        $this->authorizer = new Authorizer();
    }

    public function testGetIdReturnsNull(): void
    {
        $this->assertNull($this->authorizer->getId());
    }

    public function testValidGetterAndSetter(): void
    {
        $this->assertFalse($this->authorizer->isValid());
        
        $this->authorizer->setValid(true);
        $this->assertTrue($this->authorizer->isValid());
        
        $this->authorizer->setValid(null);
        $this->assertNull($this->authorizer->isValid());
    }

    public function testAccountGetterAndSetter(): void
    {
        $this->assertNull($this->authorizer->getAccount());
        
        $account = $this->createMock(Account::class);
        $this->authorizer->setAccount($account);
        $this->assertSame($account, $this->authorizer->getAccount());
        
        $this->authorizer->setAccount(null);
        $this->assertNull($this->authorizer->getAccount());
    }

    public function testAppIdGetterAndSetter(): void
    {
        $this->assertNull($this->authorizer->getAppId());
        
        $appId = 'wx_authorizer_123';
        $this->authorizer->setAppId($appId);
        $this->assertSame($appId, $this->authorizer->getAppId());
    }

    public function testAuthorizerAccessTokenGetterAndSetter(): void
    {
        $this->assertNull($this->authorizer->getAuthorizerAccessToken());
        
        $token = 'authorizer_access_token_123';
        $this->authorizer->setAuthorizerAccessToken($token);
        $this->assertSame($token, $this->authorizer->getAuthorizerAccessToken());
        
        $this->authorizer->setAuthorizerAccessToken(null);
        $this->assertNull($this->authorizer->getAuthorizerAccessToken());
    }

    public function testAccessTokenExpireTimeGetterAndSetter(): void
    {
        $this->assertNull($this->authorizer->getAccessTokenExpireTime());
        
        $expireTime = new \DateTimeImmutable('2024-12-31 23:59:59');
        $this->authorizer->setAccessTokenExpireTime($expireTime);
        $this->assertSame($expireTime, $this->authorizer->getAccessTokenExpireTime());
        
        $this->authorizer->setAccessTokenExpireTime(null);
        $this->assertNull($this->authorizer->getAccessTokenExpireTime());
    }

    public function testAuthorizerRefreshTokenGetterAndSetter(): void
    {
        $this->assertNull($this->authorizer->getAuthorizerRefreshToken());
        
        $refreshToken = 'authorizer_refresh_token_123';
        $this->authorizer->setAuthorizerRefreshToken($refreshToken);
        $this->assertSame($refreshToken, $this->authorizer->getAuthorizerRefreshToken());
        
        $this->authorizer->setAuthorizerRefreshToken(null);
        $this->assertNull($this->authorizer->getAuthorizerRefreshToken());
    }

    public function testFuncInfoGetterAndSetter(): void
    {
        $this->assertSame([], $this->authorizer->getFuncInfo());
        
        $funcInfo = [
            ['funcscope_category' => ['id' => 1]],
            ['funcscope_category' => ['id' => 2]],
        ];
        $this->authorizer->setFuncInfo($funcInfo);
        $this->assertSame($funcInfo, $this->authorizer->getFuncInfo());
    }

    public function testServerMessagesCollection(): void
    {
        $this->assertCount(0, $this->authorizer->getServerMessages());
        
        $serverMessage = $this->createMock(ServerMessage::class);
        $serverMessage->expects($this->once())
            ->method('setAuthorizer')
            ->with($this->authorizer);
        
        $this->authorizer->addServerMessage($serverMessage);
        $this->assertCount(1, $this->authorizer->getServerMessages());
        $this->assertTrue($this->authorizer->getServerMessages()->contains($serverMessage));
        
        // 测试重复添加
        $this->authorizer->addServerMessage($serverMessage);
        $this->assertCount(1, $this->authorizer->getServerMessages());
    }

    public function testRemoveServerMessage(): void
    {
        $serverMessage = $this->createMock(ServerMessage::class);
        $serverMessage->expects($this->exactly(2))
            ->method('setAuthorizer')
            ->willReturnCallback(function ($authorizer) use ($serverMessage) {
                static $callCount = 0;
                $callCount++;
                if ($callCount === 1) {
                    $this->assertSame($this->authorizer, $authorizer);
                } else {
                    $this->assertNull($authorizer);
                }
                return $serverMessage;
            });
        $serverMessage->expects($this->once())
            ->method('getAuthorizer')
            ->willReturn($this->authorizer);
        
        $this->authorizer->addServerMessage($serverMessage);
        $this->assertCount(1, $this->authorizer->getServerMessages());
        
        $this->authorizer->removeServerMessage($serverMessage);
        $this->assertCount(0, $this->authorizer->getServerMessages());
    }

    public function testCreatedFromIpGetterAndSetter(): void
    {
        $this->assertNull($this->authorizer->getCreatedFromIp());
        
        $ip = '192.168.1.1';
        $this->authorizer->setCreatedFromIp($ip);
        $this->assertSame($ip, $this->authorizer->getCreatedFromIp());
        
        $this->authorizer->setCreatedFromIp(null);
        $this->assertNull($this->authorizer->getCreatedFromIp());
    }

    public function testUpdatedFromIpGetterAndSetter(): void
    {
        $this->assertNull($this->authorizer->getUpdatedFromIp());
        
        $ip = '192.168.1.2';
        $this->authorizer->setUpdatedFromIp($ip);
        $this->assertSame($ip, $this->authorizer->getUpdatedFromIp());
        
        $this->authorizer->setUpdatedFromIp(null);
        $this->assertNull($this->authorizer->getUpdatedFromIp());
    }

    public function testRetrievePlainArray(): void
    {
        $appId = 'wx_authorizer_123';
        $this->authorizer->setAppId($appId);
        
        $plainArray = $this->authorizer->retrievePlainArray();
        
        $this->assertArrayHasKey('id', $plainArray);
        $this->assertArrayHasKey('appId', $plainArray);
        $this->assertNull($plainArray['id']);
        $this->assertSame($appId, $plainArray['appId']);
    }

    public function testAccessTokenMethods(): void
    {
        $token = 'authorizer_access_token_123';
        $this->authorizer->setAuthorizerAccessToken($token);
        
        $this->assertSame($token, $this->authorizer->getAccessToken());
        $this->assertSame('access_token', $this->authorizer->getAccessTokenKeyName());
    }

    public function testToString(): void
    {
        $this->assertSame('', (string) $this->authorizer);
    }
}