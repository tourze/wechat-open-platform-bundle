<?php

namespace WechatOpenPlatformBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\WechatOfficialAccountContracts\UserInterface;
use WechatOpenPlatformBundle\Entity\OfficialAccount;
use WechatOpenPlatformBundle\Event\OAuthGetBaseUserInfoEvent;

class OAuthGetBaseUserInfoEventTest extends TestCase
{
    public function testEventInheritance(): void
    {
        $event = new OAuthGetBaseUserInfoEvent();
        $this->assertInstanceOf(Event::class, $event);
    }

    public function testAccountGetterSetter(): void
    {
        $event = new OAuthGetBaseUserInfoEvent();
        $account = $this->createMock(OfficialAccount::class);
        
        $event->setAccount($account);
        $this->assertSame($account, $event->getAccount());
    }

    public function testUserGetterSetter(): void
    {
        $event = new OAuthGetBaseUserInfoEvent();
        $user = $this->createMock(UserInterface::class);
        
        $event->setUser($user);
        $this->assertSame($user, $event->getUser());
    }

    public function testResponseGetterSetter(): void
    {
        $event = new OAuthGetBaseUserInfoEvent();
        
        // 默认为 null
        $this->assertNull($event->getResponse());
        
        // 设置 Response
        $response = new Response('test content');
        $event->setResponse($response);
        $this->assertSame($response, $event->getResponse());
        
        // 可以设置为 null
        $event->setResponse(null);
        $this->assertNull($event->getResponse());
    }
}