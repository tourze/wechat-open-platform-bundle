<?php

namespace WechatOpenPlatformBundle\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\WechatOfficialAccountContracts\UserInterface;
use WechatOpenPlatformBundle\Entity\OfficialAccount as Account;

class OAuthGetBaseUserInfoEvent extends Event
{
    private Account $account;

    private UserInterface $user;

    private ?Response $response = null;

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): void
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
