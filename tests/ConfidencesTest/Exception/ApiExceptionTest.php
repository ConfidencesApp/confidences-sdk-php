<?php

namespace ConfidencesTest;

use ConfidencesTest\TestCase;
use Confidences\Confidences;
use Confidences\Exception;
use Confidences\Api\ApiRequestor;

class ApiExceptionTest extends TestCase
{
    public function testEmptyResponseBody()
    {
        try {
            $apiRequestor = new ApiRequestor();
            $apiRequestor->handleApiError('', 401, [], []);
        } catch (Exception\ApiException $e) {
            $this->assertSame(401, $e->getHttpStatus());
        }
    }
    
    public function testUnknownApiError()
    {
        try {
            $apiRequestor = new ApiRequestor();
            $apiRequestor->handleApiError('', 500, [], ['error' => ['message' => 'internal server error', 'code' => '500']]);
        } catch (Exception\ApiException $e) {
            $this->assertSame(500, $e->getHttpStatus());
            $this->assertSame('internal server error', $e->getMessage());
        }
    }
}
