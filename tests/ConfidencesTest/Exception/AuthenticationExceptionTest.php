<?php

namespace ConfidencesTest\Exception;

use ConfidencesTest\TestCase;
use Confidences\Confidences;
use Confidences\Exception;
use Confidences\Survey;
use Confidences\Api\ApiRequestor;
use Confidences\Exception\AuthenticationException;

class AuthenticationExceptionTest extends TestCase
{
    public function testInvalidCredentials()
    {
        Confidences::setVerifySslCerts(false);
        
        try {
            //$this->mockUnauthenticatedRequest();
            Confidences::setApiKey('invalidapikey');
            Survey::share($this->getMockCampaignToken(), $this->getMockRecipient(), $this->getMockMergeMap());
        } catch (Exception\AuthenticationException $e) {
            $this->assertSame(401, $e->getHttpStatus());
        }
    }
    
    public function testInvalidApiKey()
    {
        try {
            $apiRequestor = new ApiRequestor();
            Confidences::setApiKey(null);
            $apiRequestor->request('post', '/survey/share');
        } catch (Exception\AuthenticationException $e) {
            $this->assertNull($e->getHttpStatus());
            $this->assertContains('Confidences::setApiKey(<API-KEY>)', $e->getMessage());
        }
    }
}
