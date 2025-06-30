<?php

namespace WechatOpenPlatformBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;
use WechatOpenPlatformBundle\Entity\ServerMessage;
use WechatOpenPlatformBundle\Event\WechatOpenPlatformServerMessageResponseEvent;

class WechatOpenPlatformServerMessageResponseEventTest extends TestCase
{
    public function testEventInheritance(): void
    {
        $event = new WechatOpenPlatformServerMessageResponseEvent();
        $this->assertInstanceOf(Event::class, $event);
    }

    public function testMessageGetterSetter(): void
    {
        $event = new WechatOpenPlatformServerMessageResponseEvent();
        $message = $this->createMock(ServerMessage::class);
        
        $event->setMessage($message);
        $this->assertSame($message, $event->getMessage());
    }
}