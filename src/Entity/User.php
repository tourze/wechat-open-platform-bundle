<?php

namespace WechatOpenPlatformBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\WechatOfficialAccountContracts\OfficialAccountInterface;
use Tourze\WechatOfficialAccountContracts\UserInterface;

/**
 * 临时的 User 实体，用于解决依赖问题
 */
#[ORM\Entity]
#[ORM\Table(name: 'wechat_open_platform_user', options: ['comment' => '用户信息'])]
class User implements UserInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => 'OpenID'])]
    private string $openId;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'UnionID'])]
    private ?string $unionId = null;

    #[ORM\ManyToOne(targetEntity: OfficialAccount::class)]
    private ?OfficialAccount $account = null;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true, options: ['comment' => '语言'])]
    private ?string $language = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '关注时间'])]
    private ?\DateTimeImmutable $subscribeTime = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '关注场景'])]
    private ?string $subscribeScene = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否关注'])]
    private bool $subscribed = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpenId(): string
    {
        return $this->openId;
    }

    public function setOpenId(string $openId): self
    {
        $this->openId = $openId;
        return $this;
    }

    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    public function setUnionId(?string $unionId): self
    {
        $this->unionId = $unionId;
        return $this;
    }

    public function getAccount(): ?OfficialAccount
    {
        return $this->account;
    }

    public function setAccount(?OfficialAccount $account): self
    {
        $this->account = $account;
        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function getSubscribeTime(): ?\DateTimeImmutable
    {
        return $this->subscribeTime;
    }

    public function setSubscribeTime(?\DateTimeImmutable $subscribeTime): self
    {
        $this->subscribeTime = $subscribeTime;
        return $this;
    }

    public function getSubscribeScene(): ?string
    {
        return $this->subscribeScene;
    }

    public function setSubscribeScene(?string $subscribeScene): self
    {
        $this->subscribeScene = $subscribeScene;
        return $this;
    }

    public function isSubscribed(): bool
    {
        return $this->subscribed;
    }

    public function setSubscribed(bool $subscribed): self
    {
        $this->subscribed = $subscribed;
        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        // TODO: 实现头像URL
        return null;
    }

    public function getOfficialAccount(): ?OfficialAccountInterface
    {
        // TODO: 账号需要实现 OfficialAccountInterface
        return null;
    }

    public function __toString(): string
    {
        return $this->openId ?? '';
    }
}