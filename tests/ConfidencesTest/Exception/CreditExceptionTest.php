<?php

namespace ConfidencesTest;

use ConfidencesTest\TestCase;
use Confidences\Confidences;
use Confidences\Exception;
use Confidences\Survey;

class CreditExceptionTest extends TestCase
{
    public function testDecline()
    {
        Confidences::setVerifySslCerts(false);
        
        try {
            $this->mockInsufficientCreditRequest();
            Survey::share($this->getMockCampaignToken(), $this->getMockRecipient(), $this->getMockMergeMap());
        } catch (Exception\CreditException $e) {
            $this->assertSame(402, $e->getHttpStatus());
            $this->assertSame(402, $e->getConfidencesCode());
            $actual = $e->getJsonBody();
            $this->assertSame(
                ['error' => [
                    'code' => 402,
                    'message' => 'Insufficient funds.',
                    'params' => $this->getMockRequestParams(),
                ]],
                $actual
            );
        }
    }
}
