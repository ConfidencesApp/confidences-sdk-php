<?php
namespace ConfidencesTest;

use Confidences\Confidences;
use Confidences\Survey;
use Confidences\Exception\BaseException;

class SurveyTest extends TestCase
{
    public function testSuccessShare()
    {
        Confidences::setVerifySslCerts(false);
        $this->mockSuccessShareRequest();
        
        $result = Survey::share($this->getMockCampaignToken(), $this->getMockRecipient(), $this->getMockMergeMap());
        $this->assertTrue($result);
        
        $exception = Survey::getException();
        $this->assertNull($exception);
    }
    
    public function testFailedShare()
    {
        Confidences::setVerifySslCerts(false);
        $this->mockInvalidRecipientRequest();
        
        $result = Survey::share($this->getMockCampaignToken(), $this->getMockRecipient(), $this->getMockMergeMap());
        $this->assertFalse($result);
        
        $exception = Survey::getException();
        $this->assertNotNull($exception);
        $this->assertInstanceOf(BaseException::class, $exception);
    }
    
    public function testInvalidCampaignToken()
    {
        $this->expectException(\InvalidArgumentException::class);
        Survey::share('', $this->getMockRecipient());
    }
    
    public function testInvalidRecipient()
    {
        $this->expectException(\InvalidArgumentException::class);
        Survey::share($this->getMockCampaignToken(), '');
    }
}
