<?php

namespace ConfidencesTest;

use Confidences\HttpClient\CurlClient;

class CurlClientTest extends TestCase
{
    public function testTimeout()
    {
        $curl = new CurlClient();
        $this->assertSame(CurlClient::DEFAULT_TIMEOUT, $curl->getTimeout());
        $this->assertSame(CurlClient::DEFAULT_CONNECT_TIMEOUT, $curl->getConnectTimeout());

        // implicitly tests whether we're returning the CurlClient instance
        $curl = $curl->setConnectTimeout(1)->setTimeout(10);
        $this->assertSame(1, $curl->getConnectTimeout());
        $this->assertSame(10, $curl->getTimeout());

        $curl->setTimeout(-1);
        $curl->setConnectTimeout(-999);
        $this->assertSame(0, $curl->getTimeout());
        $this->assertSame(0, $curl->getConnectTimeout());
    }

    public function testDefaultOptions()
    {
        // make sure options array loads/saves properly
        $optionsArray = [CURLOPT_PROXY => 'localhost:80'];
        $withOptionsArray = new CurlClient($optionsArray);
        $this->assertSame($withOptionsArray->getDefaultOptions(), $optionsArray);

        // make sure closure-based options work properly, including argument passing
        $ref = null;
        $withClosure = new CurlClient(
            function ($method, $absUrl, $headers, $params, $hasFile) use (&$ref) {
                $ref = func_get_args();
                return [];
            }
        );

        $withClosure->request('get', 'https://httpbin.org/status/200', [], [], false);
        $this->assertSame($ref, ['get', 'https://httpbin.org/status/200', [], [], false]);

        // this is the last test case that will run, since it'll throw an exception at the end
        $withBadClosure = new CurlClient(
            function () {
                return 'thisShouldNotWork';
            }
        );
        $this->setExpectedException('Confidences\Exception\ApiException', "Non-array value returned by defaultOptions CurlClient callback");
        $withBadClosure->request('get', 'https://httpbin.org/status/200', [], [], false);
    }

    public function testEncode()
    {
        $a = [
            'my' => 'value',
            'that' => ['your' => 'example'],
            'bar' => 1,
            'baz' => null
        ];

        $enc = CurlClient::encode($a);
        $this->assertSame('my=value&that%5Byour%5D=example&bar=1', $enc);

        $a = ['that' => ['your' => 'example', 'foo' => null]];
        $enc = CurlClient::encode($a);
        $this->assertSame('that%5Byour%5D=example', $enc);

        $a = ['that' => 'example', 'foo' => ['bar', 'baz']];
        $enc = CurlClient::encode($a);
        $this->assertSame('that=example&foo%5B%5D=bar&foo%5B%5D=baz', $enc);

        $a = [
            'my' => 'value',
            'that' => ['your' => ['cheese', 'whiz', null]],
            'bar' => 1,
            'baz' => null
        ];

        $enc = CurlClient::encode($a);
        $expected = 'my=value&that%5Byour%5D%5B%5D=cheese'
              . '&that%5Byour%5D%5B%5D=whiz&bar=1';
        $this->assertSame($expected, $enc);

        // Ignores an empty array
        $enc = CurlClient::encode(['foo' => [], 'bar' => 'baz']);
        $expected = 'bar=baz';
        $this->assertSame($expected, $enc);

        $a = ['foo' => [['bar' => 'baz'], ['bar' => 'bin']]];
        $enc = CurlClient::encode($a);
        $this->assertSame('foo%5B0%5D%5Bbar%5D=baz&foo%5B1%5D%5Bbar%5D=bin', $enc);
    }
}
