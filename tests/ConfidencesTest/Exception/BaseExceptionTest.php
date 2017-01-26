<?php

namespace ConfidencesTest;

use ConfidencesTest\TestCase;
use Confidences\Exception;

class BaseExceptionTest extends TestCase
{
    public function testDefaultConstructor()
    {
        $baseException = new Exception\ApiException('error message');
        $this->assertEquals('error message', $baseException->getMessage());
        $this->assertNull($baseException->getHttpStatus());
        $this->assertNull($baseException->getHttpBody());
        $this->assertNull($baseException->getJsonBody());
        $this->assertNull($baseException->getHttpHeaders());
        $this->assertNull($baseException->getRequestId());
    }
    
    public function testFullConstructor()
    {
        $bodyData = [
            'code' => 'code',
            'message' => 'message'
        ];
        
        $httpHeaders = [
            'Request-Id' => '123456789'
        ];
        
        $baseException = new Exception\ApiException('error message', 200, json_encode($bodyData), $bodyData, $httpHeaders);
        $this->assertEquals('error message', $baseException->getMessage());
        $this->assertEquals(200, $baseException->getHttpStatus());
        $this->assertEquals(json_encode($bodyData), $baseException->getHttpBody());
        $this->assertEquals($bodyData, $baseException->getJsonBody());
        $this->assertEquals($httpHeaders, $baseException->getHttpHeaders());
        $this->assertEquals('123456789', $baseException->getRequestId());
    }
}
