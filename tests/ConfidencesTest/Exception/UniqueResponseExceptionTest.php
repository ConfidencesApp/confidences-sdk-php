<?php

namespace ConfidencesTest;

use ConfidencesTest\TestCase;
use Confidences\Confidences;
use Confidences\Exception;
use Confidences\Survey;

class UniqueResponseExceptionTest extends TestCase
{
    public function testBadData()
    {
        Confidences::setVerifySslCerts(false);

        try {
            $this->mockUniqueResponseRequest();
            Survey::share($this->getMockCampaignToken(), $this->getMockRecipient(), $this->getMockMergeMap());
        } catch (Exception\InvalidRequestException $e) {
            $this->assertSame(422, $e->getHttpStatus());
            $this->assertSame($this->getMockMergeMap(), $e->getConfidencesParam());
        }
    }
}
