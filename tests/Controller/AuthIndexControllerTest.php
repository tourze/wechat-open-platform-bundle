<?php

namespace WechatOpenPlatformBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WechatOpenPlatformBundle\Controller\AuthIndexController;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Request\CreatePreAuthCodeRequest;
use WechatOpenPlatformBundle\Service\ApiService;

class AuthIndexControllerTest extends TestCase
{
    private $apiService;
    private $controller;
    private $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiService = $this->createMock(ApiService::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->controller = new class($this->apiService, $this->urlGenerator) extends AuthIndexController {
            private $urlGenerator;
            
            public function __construct($apiService, $urlGenerator)
            {
                parent::__construct($apiService);
                $this->urlGenerator = $urlGenerator;
            }
            
            protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
            {
                return $this->urlGenerator->generate($route, $parameters, $referenceType);
            }
            
            protected function render(string $view, array $parameters = [], ?\Symfony\Component\HttpFoundation\Response $response = null): \Symfony\Component\HttpFoundation\Response
            {
                return new \Symfony\Component\HttpFoundation\Response(json_encode($parameters));
            }
        };
    }

    public function testInvokeWithoutBizAppId(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('getAppId')->willReturn('test_app_id');

        $request = Request::create('/wechat-open-platform/auth/test');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('wechat-open-platform-auth-callback', ['appId' => 'test_app_id'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://example.com/callback');

        $this->apiService->expects($this->once())
            ->method('request')
            ->with($this->isInstanceOf(CreatePreAuthCodeRequest::class))
            ->willReturn(['pre_auth_code' => 'test_pre_auth_code']);

        $response = $this->controller->__invoke($account, $request);
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('component_appid=test_app_id', $content['url']);
        $this->assertStringContainsString('pre_auth_code=test_pre_auth_code', $content['url']);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fexample.com%2Fcallback', $content['url']);
    }

    public function testInvokeWithBizAppId(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('getAppId')->willReturn('test_app_id');

        $request = Request::create('/wechat-open-platform/auth/test', 'GET', ['biz_appid' => 'specific_app_id']);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('https://example.com/callback');

        $this->apiService->expects($this->once())
            ->method('request')
            ->willReturn(['pre_auth_code' => 'test_pre_auth_code']);

        $response = $this->controller->__invoke($account, $request);
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('biz_appid=specific_app_id', $content['url']);
    }
}