<?php

namespace WechatOpenPlatformBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Exception\NotImplementedException;

class NotImplementedExceptionTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $exception = new NotImplementedException();
        $this->assertInstanceOf(NotImplementedException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testWithMessage(): void
    {
        $message = 'This feature is not implemented';
        $exception = new NotImplementedException($message);
        $this->assertSame($message, $exception->getMessage());
    }

    public function testWithMessageAndCode(): void
    {
        $message = 'Not implemented';
        $code = 501;
        $exception = new NotImplementedException($message, $code);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testWithPreviousException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new NotImplementedException('Not implemented', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}