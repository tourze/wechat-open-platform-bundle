<?php

namespace WechatOpenPlatformBundle\Tests\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Client\ApiClient;
use HttpClientBundle\Exception\HttpClientException;
use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Request\GetComponentAccessTokenRequest;
use WechatOpenPlatformBundle\Request\WithAccountRequest;
use WechatOpenPlatformBundle\Service\ApiService;

class ApiServiceTest extends TestCase
{
    private $entityManager;
    private $apiService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->apiService = new ApiService($this->entityManager);
    }

    public function testInheritance(): void
    {
        $this->assertInstanceOf(ApiClient::class, $this->apiService);
    }

    public function testGetBaseUrl(): void
    {
        $this->assertEquals('https://api.weixin.qq.com', $this->apiService->getBaseUrl());
    }

    public function testGetRequestMethodWithNull(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestMethod')->willReturn(null);
        
        $reflection = new \ReflectionClass($this->apiService);
        $method = $reflection->getMethod('getRequestMethod');
        $method->setAccessible(true);
        
        $this->assertEquals('POST', $method->invoke($this->apiService, $request));
    }

    public function testGetRequestMethodWithValue(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestMethod')->willReturn('GET');
        
        $reflection = new \ReflectionClass($this->apiService);
        $method = $reflection->getMethod('getRequestMethod');
        $method->setAccessible(true);
        
        $this->assertEquals('GET', $method->invoke($this->apiService, $request));
    }

    public function testGetRequestOptionsWithNonAccountRequest(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $options = ['key' => 'value'];
        $request->method('getRequestOptions')->willReturn($options);
        
        $reflection = new \ReflectionClass($this->apiService);
        $method = $reflection->getMethod('getRequestOptions');
        $method->setAccessible(true);
        
        $this->assertEquals($options, $method->invoke($this->apiService, $request));
    }

    public function testFormatResponseWithSuccessfulResponse(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('{"result": "success"}');
        
        $reflection = new \ReflectionClass($this->apiService);
        $method = $reflection->getMethod('formatResponse');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->apiService, $request, $response);
        $this->assertEquals(['result' => 'success'], $result);
    }

    public function testFormatResponseWithError(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('{"errcode": 40001, "errmsg": "Invalid credential"}');
        
        $reflection = new \ReflectionClass($this->apiService);
        $method = $reflection->getMethod('formatResponse');
        $method->setAccessible(true);
        
        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('Invalid credential');
        $this->expectExceptionCode(40001);
        
        $method->invoke($this->apiService, $request, $response);
    }

    public function testFormatResponseWithZeroErrorCode(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('{"errcode": 0, "errmsg": "ok", "data": "test"}');
        
        $reflection = new \ReflectionClass($this->apiService);
        $method = $reflection->getMethod('formatResponse');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->apiService, $request, $response);
        $this->assertEquals(['errcode' => 0, 'errmsg' => 'ok', 'data' => 'test'], $result);
    }
}