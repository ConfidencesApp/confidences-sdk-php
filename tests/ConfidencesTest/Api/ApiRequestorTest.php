<?php

namespace ConfidencesTest\Api;

use Confidences\HttpClient\CurlClient;
use Confidences\Api\ApiRequestor;
use ConfidencesTest\TestCase;
use Confidences\Confidences;

class ApiRequestorTest extends TestCase
{
    public function testEncodeObjects()
    {
        $reflector = new \ReflectionClass('Confidences\\Api\\ApiRequestor');
        $method = $reflector->getMethod('_encodeObjects');
        $method->setAccessible(true);
        
        // Preserves Boolean
        $v = true;
        $enc = $method->invoke(null, $v);
        $this->assertSame($enc, 'true');
        
        // Preserves Boolean
        $v = false;
        $enc = $method->invoke(null, $v);
        $this->assertSame($enc, 'false');
        
        // Preserves UTF-8
        $v = ['test' => "☃"];
        $enc = $method->invoke(null, $v);
        $this->assertSame($enc, $v);

        // Encodes latin-1 -> UTF-8
        $v = ['test' => "\xe9"];
        $enc = $method->invoke(null, $v);
        $this->assertSame($enc, ['test' => "\xc3\xa9"]);
    }

    public function testHttpClientInjection()
    {
        $reflector = new \ReflectionClass('Confidences\\Api\\ApiRequestor');
        $method = $reflector->getMethod('httpClient');
        $method->setAccessible(true);

        $curl = new CurlClient();
        $curl->setTimeout(10);
        ApiRequestor::setHttpClient($curl);

        $injectedCurl = $method->invoke(new ApiRequestor());
        $this->assertSame($injectedCurl, $curl);
    }
    
    public function testDefaultHeaders()
    {
        $reflector = new \ReflectionClass('Confidences\\Api\\ApiRequestor');
        $method = $reflector->getMethod('_defaultHeaders');
        $method->setAccessible(true);
    
        // no way to stub static methods with PHPUnit 4.x :(
        Confidences::setAppInfo('MyTestApp', '1.2.34', 'https://mytestapp.example');
        $apiKey = '9ABx7XJix3K4xMkgvt6Tc9Zu2iZU1ePR';
    
        $headers = $method->invoke(null, $apiKey);
    
        $ua = json_decode($headers['X-Confidences-Client-User-Agent']);
        $this->assertSame($ua->application->name, 'MyTestApp');
        $this->assertSame($ua->application->version, '1.2.34');
        $this->assertSame($ua->application->url, 'https://mytestapp.example');
    
        $this->assertSame(
            $headers['User-Agent'],
            'Confidences/v1 PhpBindings/' . Confidences::VERSION . ' MyTestApp/1.2.34 (https://mytestapp.example)'
        );
    
        $this->assertSame($headers['Authorization'], 'Bearer ' . $apiKey);
    }
}
