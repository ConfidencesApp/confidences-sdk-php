<?php

namespace ConfidencesTest;

use Confidences\Api\ApiRequestor;
use Confidences\Confidences;
use Confidences\HttpClient;

/**
 * Base class for Confidences test cases, provides some utility methods for creating
 * objects.
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    const API_KEY = '042FuyjdGorpklFmw03bkf1Hn7dhG2WQ';

    private $mock;

    protected static function authorizeFromEnv()
    {
        $apiKey = getenv('CONFIDENCES_API_KEY');
        if (!$apiKey) {
            $apiKey = self::API_KEY;
        }

        Confidences::setApiKey($apiKey);
        Confidences::setApiBase('https://api.confidences.co/api');
    }

    protected function setUp()
    {
        ApiRequestor::setHttpClient(HttpClient\CurlClient::instance());
        $this->mock = null;
        $this->call = 0;
    }
    
    protected function getMockCampaignToken()
    {
        return 'campaignToken';
    }
    
    protected function getMockRecipient()
    {
        return 'mobileOrEmail';
    }
    
    protected function getMockMergeMap()
    {
        return [
            'var1' => 'value1',
            'var2' => 'value2'
        ];
    }
    
    protected function getMockRequestParams()
    {
        return [
            'recipient' => $this->getMockRecipient(),
            'campaign_token' => $this->getMockCampaignToken(),
            'merge_map' => $this->getMockMergeMap()
        ];
    }

    protected function mockSuccessShareRequest()
    {
        $successObject = [
            'result' => 'sent'
        ];
    
        return $this->mockRequest(
            'post',
            '/api/survey/share',
            $this->getMockRequestParams(),
            ['data' => $successObject],
            200
        );
    }
    
    protected function mockInvalidRecipientRequest()
    {
        $errorObject = [
            'code' => 400,
            'message' => 'Bad request argument, check requested recipient.',
            'params' => $this->getMockRequestParams()
        ];
    
        return $this->mockRequest(
            'post',
            '/api/survey/share',
            $this->getMockRequestParams(),
            ['error' => $errorObject],
            400
        );
    }
    
    protected function mockUnauthenticatedRequest()
    {
        $errorObject = [
            'code' => 401,
            'message' => 'Access Denied.',
            'params' => $this->getMockRequestParams()
        ];
    
        return $this->mockRequest(
            'post',
            '/api/survey/share',
            $this->getMockRequestParams(),
            ['error' => $errorObject],
            401
        );
    }

    protected function mockInsufficientCreditRequest()
    {
        $errorObject = [
            'code' => 402,
            'message' => 'Insufficient funds.',
            'params' => $this->getMockRequestParams()
        ];
    
        return $this->mockRequest(
            'post',
            '/api/survey/share',
            $this->getMockRequestParams(),
            ['error' => $errorObject],
            402
        );
    }
    
    protected function mockUnauthorizeRequest()
    {
        $errorObject = [
            'code' => 403,
            'message' => 'You are unabled to use API survey share feature.',
            'params' => $this->getMockRequestParams()
        ];
    
        return $this->mockRequest(
            'post',
            '/api/survey/share',
            $this->getMockRequestParams(),
            ['error' => $errorObject],
            403
        );
    }
    
    protected function mockNotFoundCampaignRequest()
    {
        $errorObject = [
            'code' => 404,
            'message' => 'Campaign not found.',
            'params' => $this->getMockRequestParams()
        ];
    
        return $this->mockRequest(
            'post',
            '/api/survey/share',
            $this->getMockRequestParams(),
            ['error' => $errorObject],
            404
        );
    }
    
    protected function mockRequest($method, $path, $params = [], $return = ['id' => 'myId'], $rcode = 200)
    {
        $mock = $this->setUpMockRequest();
        $mock->expects($this->at($this->call++))
            ->method('request')
            ->with(strtolower($method), 'https://api.confidences.co' . $path, $this->anything(), $params, false)
            ->willReturn([json_encode($return), $rcode, ['Request-Id' => $this->call]]);
    }

    private function setUpMockRequest()
    {
        if (!$this->mock) {
            self::authorizeFromEnv();
            $this->mock = $this->createMock('\Confidences\HttpClient\ClientInterface');
            ApiRequestor::setHttpClient($this->mock);
        }
        return $this->mock;
    }
}
