<?php

namespace WechatOpenPlatformBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use WechatOpenPlatformBundle\Repository\AuthCodeRepository;

#[ORM\Entity(repositoryClass: AuthCodeRepository::class)]
#[ORM\Table(name: 'wechat_open_platform_auth_code', options: ['comment' => '微信开放平台AuthCode'])]
class AuthCode implements \Stringable
{
    use CreateTimeAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Account $account = null;

    #[ORM\Column(length: 64, unique: true, options: ['comment' => '授权码'])]
    private ?string $authCode = null;

    #[ORM\Column(nullable: true, options: ['comment' => '返回结果'])]
    private ?array $result = null;

    #[CreateIpColumn]
    #[ORM\Column(length: 45, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;


    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): static
    {
        $this->account = $account;

        return $this;
    }

    public function getAuthCode(): ?string
    {
        return $this->authCode;
    }

    public function setAuthCode(string $authCode): static
    {
        $this->authCode = $authCode;

        return $this;
    }

    public function getResult(): ?array
    {
        return $this->result;
    }

    public function setResult(?array $result): static
    {
        $this->result = $result;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): void
    {
        $this->createdFromIp = $createdFromIp;
    }


    public function __toString(): string
    {
        return sprintf('%s #%s', 'AuthCode', $this->id ?? 'new');
    }
}
