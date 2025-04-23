<?php

namespace WechatOpenPlatformBundle\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Entity\User;

class OAuthGetBaseUserInfoEvent extends Event
{
    private Account $account;

    private User $user;

    private ?Response $response = null;

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }
}
