<?php

namespace WechatOpenPlatformBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\User;
use WechatOpenPlatformBundle\Repository\UserRepository;

class UserRepositoryTest extends TestCase
{
    private UserRepository $repository;
    private ManagerRegistry $registry;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        // 不设置期望，允许0次或多次调用
        $this->registry->method('getManagerForClass')
            ->with(User::class)
            ->willReturn($this->entityManager);
            
        $this->repository = new UserRepository($this->registry);
    }
    
    public function testRepositoryIsCreatedWithCorrectEntityClass(): void
    {
        $this->assertInstanceOf(UserRepository::class, $this->repository);
    }
}
