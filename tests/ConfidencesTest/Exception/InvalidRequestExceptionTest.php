<?php

namespace ConfidencesTest;

use ConfidencesTest\TestCase;
use Confidences\Confidences;
use Confidences\Exception;
use Confidences\Survey;

class InvalidRequestExceptionTest extends TestCase
{
    public function testInvalidObject()
    {
        Confidences::setVerifySslCerts(false);

        try {
            $this->mockNotFoundCampaignRequest();
            Survey::share($this->getMockCampaignToken(), $this->getMockRecipient(), $this->getMockMergeMap());
        } catch (Exception\InvalidRequestException $e) {
            $this->assertSame(404, $e->getHttpStatus());
        }
    }

    public function testBadData()
    {
        Confidences::setVerifySslCerts(false);

        try {
            $this->mockInvalidRecipientRequest();
            Survey::share($this->getMockCampaignToken(), $this->getMockRecipient(), $this->getMockMergeMap());
        } catch (Exception\InvalidRequestException $e) {
            $this->assertSame(400, $e->getHttpStatus());
            $this->assertSame($this->getMockMergeMap(), $e->getConfidencesParam());
        }
    }
}
