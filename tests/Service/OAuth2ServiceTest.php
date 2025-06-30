<?php

namespace WechatOpenPlatformBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Exception\NotImplementedException;
use WechatOpenPlatformBundle\Service\OAuth2Service;

class OAuth2ServiceTest extends TestCase
{
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OAuth2Service();
    }

    public function testGetUserInfoByCodeThrowsNotImplementedException(): void
    {
        $this->expectException(NotImplementedException::class);
        $this->expectExceptionMessage('getUserInfoByCode method needs to be reimplemented');

        $this->service->getUserInfoByCode('test_code');
    }

    public function testGetUserInfoByCodeWithAppIdThrowsNotImplementedException(): void
    {
        $this->expectException(NotImplementedException::class);
        $this->expectExceptionMessage('getUserInfoByCode method needs to be reimplemented');

        $this->service->getUserInfoByCode('test_code', 'test_app_id');
    }
}