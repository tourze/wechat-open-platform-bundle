<?php

namespace WechatOpenPlatformBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use WechatOpenPlatformBundle\Entity\ServerMessage;

class WechatOpenPlatformServerMessageResponseEvent extends Event
{
    private ServerMessage $message;

    public function getMessage(): ServerMessage
    {
        return $this->message;
    }

    public function setMessage(ServerMessage $message): void
    {
        $this->message = $message;
    }
}
