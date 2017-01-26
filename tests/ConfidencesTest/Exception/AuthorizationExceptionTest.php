<?php

namespace ConfidencesTest\Exception;

use ConfidencesTest\TestCase;
use Confidences\Confidences;
use Confidences\Exception;
use Confidences\Survey;

class AuthorizationExceptionTest extends TestCase
{
    public function testInvalidCredentials()
    {
        Confidences::setVerifySslCerts(false);
        
        try {
            $this->mockUnauthorizeRequest();
            Survey::share($this->getMockCampaignToken(), $this->getMockRecipient(), $this->getMockMergeMap());
        } catch (Exception\AuthorizationException $e) {
            $this->assertSame(403, $e->getHttpStatus());
        }
    }
}
