<?php

namespace WechatOpenPlatformBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\Authorizer;
use WechatOpenPlatformBundle\Entity\ServerMessage;

class ServerMessageTest extends TestCase
{
    private ServerMessage $serverMessage;

    protected function setUp(): void
    {
        $this->serverMessage = new ServerMessage();
    }

    public function testGetIdReturnsZeroByDefault(): void
    {
        $this->assertSame(0, $this->serverMessage->getId());
    }

    public function testAccountGetterAndSetter(): void
    {
        $this->assertNull($this->serverMessage->getAccount());
        
        $account = $this->createMock(Account::class);
        $this->serverMessage->setAccount($account);
        $this->assertSame($account, $this->serverMessage->getAccount());
        
        $this->serverMessage->setAccount(null);
        $this->assertNull($this->serverMessage->getAccount());
    }

    public function testMessageGetterAndSetter(): void
    {
        $this->assertSame([], $this->serverMessage->getMessage());
        
        $message = [
            'AppId' => 'wx123',
            'CreateTime' => '1234567890',
            'InfoType' => 'component_verify_ticket',
            'ComponentVerifyTicket' => 'ticket123'
        ];
        $this->serverMessage->setMessage($message);
        $this->assertSame($message, $this->serverMessage->getMessage());
    }

    public function testResponseGetterAndSetter(): void
    {
        $this->assertNull($this->serverMessage->getResponse());
        
        $response = [
            'status' => 'success',
            'message' => 'Ticket updated successfully'
        ];
        $this->serverMessage->setResponse($response);
        $this->assertSame($response, $this->serverMessage->getResponse());
        
        $this->serverMessage->setResponse(null);
        $this->assertNull($this->serverMessage->getResponse());
    }

    public function testAuthorizerGetterAndSetter(): void
    {
        $this->assertNull($this->serverMessage->getAuthorizer());
        
        $authorizer = $this->createMock(Authorizer::class);
        $this->serverMessage->setAuthorizer($authorizer);
        $this->assertSame($authorizer, $this->serverMessage->getAuthorizer());
        
        $this->serverMessage->setAuthorizer(null);
        $this->assertNull($this->serverMessage->getAuthorizer());
    }

    public function testCreatedFromIpGetterAndSetter(): void
    {
        $this->assertNull($this->serverMessage->getCreatedFromIp());
        
        $ip = '192.168.1.1';
        $this->serverMessage->setCreatedFromIp($ip);
        $this->assertSame($ip, $this->serverMessage->getCreatedFromIp());
        
        $this->serverMessage->setCreatedFromIp(null);
        $this->assertNull($this->serverMessage->getCreatedFromIp());
    }

    public function testToString(): void
    {
        $this->assertSame('ServerMessage #0', (string) $this->serverMessage);
        
        // 使用反射设置ID来测试非零ID的情况
        $reflection = new \ReflectionClass($this->serverMessage);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->serverMessage, 123);
        
        $this->assertSame('ServerMessage #123', (string) $this->serverMessage);
    }

    public function testToStringWithNullId(): void
    {
        // 使用反射设置ID为null
        $reflection = new \ReflectionClass($this->serverMessage);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->serverMessage, null);
        
        $this->assertSame('ServerMessage #new', (string) $this->serverMessage);
    }
}