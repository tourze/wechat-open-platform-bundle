<?php

namespace WechatOpenPlatformBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use WechatOpenPlatformBundle\Repository\ServerMessageRepository;

#[ORM\Entity(repositoryClass: ServerMessageRepository::class)]
#[ORM\Table(name: 'wechat_open_platform_server_message', options: ['comment' => '微信开放平台服务端消息'])]
class ServerMessage implements \Stringable
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
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Account $account = null;

    #[ORM\Column(options: ['comment' => '消息内容'])]
    private array $message = [];

    #[ORM\Column(nullable: true, options: ['comment' => '响应内容'])]
    private ?array $response = null;

    #[ORM\ManyToOne(inversedBy: 'serverMessages')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Authorizer $authorizer = null;

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

    public function getMessage(): array
    {
        return $this->message;
    }

    public function setMessage(array $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function setResponse(?array $response): static
    {
        $this->response = $response;

        return $this;
    }

    public function getAuthorizer(): ?Authorizer
    {
        return $this->authorizer;
    }

    public function setAuthorizer(?Authorizer $authorizer): static
    {
        $this->authorizer = $authorizer;

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
        return sprintf('%s #%s', 'ServerMessage', $this->id ?? 'new');
    }
}
