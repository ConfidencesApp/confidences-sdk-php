<?php
namespace ConfidencesTest;

use Confidences\Confidences;

class ConfidencesTest extends TestCase
{
    public function testSetAppInfo()
    {
        Confidences::setAppInfo('Confidences phpunit', '1.2.34', 'https://localhost');
        
        $this->assertInternalType('array', Confidences::$appInfo);
        $this->assertSame(['name' => 'Confidences phpunit', 'version' => '1.2.34', 'url' => 'https://localhost'], Confidences::getAppInfo());
    }
    
    public function testSetApiBase()
    {
        Confidences::setApiBase('https://localhost');
        $this->assertSame('https://localhost', Confidences::getApiBase());
    }
}
