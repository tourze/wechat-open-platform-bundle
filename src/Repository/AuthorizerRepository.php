<?php

namespace WechatOpenPlatformBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WechatOpenPlatformBundle\Entity\Authorizer;

/**
 * @method Authorizer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Authorizer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Authorizer[]    findAll()
 * @method Authorizer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorizerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Authorizer::class);
    }
}
