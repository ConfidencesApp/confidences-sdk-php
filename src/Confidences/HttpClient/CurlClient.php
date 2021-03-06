<?php

namespace Confidences\HttpClient;

use Confidences\Confidences;
use Confidences\Exception;
use Confidences\Util;

class CurlClient implements ClientInterface
{
    private static $instance;

    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected $defaultOptions;

    /**
     * CurlClient constructor.
     *
     * Pass in a callable to $defaultOptions that returns an array of CURLOPT_* values to start
     * off a request with, or an flat array with the same format used by curl_setopt_array() to
     * provide a static set of options. Note that many options are overridden later in the request
     * call, including timeouts, which can be set via setTimeout() and setConnectTimeout().
     *
     * Note that request() will silently ignore a non-callable, non-array $defaultOptions, and will
     * throw an exception if $defaultOptions returns a non-array value.
     *
     * @param array|callable|null $defaultOptions
     */
    public function __construct($defaultOptions = null)
    {
        $this->defaultOptions = $defaultOptions;
    }

    public function getDefaultOptions()
    {
        return $this->defaultOptions;
    }

    // USER DEFINED TIMEOUTS

    const DEFAULT_TIMEOUT = 80;
    const DEFAULT_CONNECT_TIMEOUT = 30;

    /**
     * @var int
     */
    private $timeout = self::DEFAULT_TIMEOUT;

    /**
     * @var int
     */
    private $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT;

    /**
     * @param  int $seconds
     * @return self
     */
    public function setTimeout(int $seconds) : self
    {
        $this->timeout = (int) max($seconds, 0);
        return $this;
    }

    /**
     * @param  $seconds
     * @return self
     */
    public function setConnectTimeout($seconds) : self
    {
        $this->connectTimeout = (int) max($seconds, 0);
        return $this;
    }
    
    /**
     * @return int
     */
    public function getTimeout() : int
    {
        return $this->timeout;
    }

    /**
     * @return int
     */
    public function getConnectTimeout() : int
    {
        return $this->connectTimeout;
    }

    // END OF USER DEFINED TIMEOUTS

    /**
     * @param  string $method
     * @param  string $absUrl
     * @param  array  $headers
     * @param  array  $params
     * @param  bool   $hasFile
     * @return array
     * @throws Exception\ApiConnectionException
     * @throws Exception\ApiException
     */
    public function request(string $method, string $absUrl, array $headers, array $params, bool $hasFile) : array
    {
        $curl = curl_init();
        $method = strtolower($method);

        $opts = [];
        if (is_callable($this->defaultOptions)) { // call defaultOptions callback, set options to return value
            $opts = call_user_func_array($this->defaultOptions, func_get_args());
            if (!is_array($opts)) {
                throw new Exception\ApiException("Non-array value returned by defaultOptions CurlClient callback");
            }
        } elseif (is_array($this->defaultOptions)) { // set default curlopts from array
            $opts = $this->defaultOptions;
        }

        if ($method == 'get') {
            if ($hasFile) {
                throw new Exception\ApiException(
                    "Issuing a GET request with a file parameter"
                );
            }
            $opts[CURLOPT_HTTPGET] = 1;
            if (count($params) > 0) {
                $encoded = self::encode($params);
                $absUrl = "$absUrl?$encoded";
            }
        } elseif ($method == 'post') {
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $hasFile ? $params : self::encode($params);
        } elseif ($method == 'delete') {
            $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            if (count($params) > 0) {
                $encoded = self::encode($params);
                $absUrl = "$absUrl?$encoded";
            }
        } else {
            throw new Exception\ApiException("Unrecognized method $method");
        }

        // Create a callback to capture HTTP headers for the response
        $rheaders = [];
        $headerCallback = function ($curl, $header_line) use (&$rheaders) {
            // Ignore the HTTP request line (HTTP/1.1 200 OK)
            if (strpos($header_line, ":") === false) {
                return strlen($header_line);
            }
            list($key, $value) = explode(":", trim($header_line), 2);
            $rheaders[trim($key)] = trim($value);
            return strlen($header_line);
        };

        $absUrl = Util\Util::utf8($absUrl);
        $opts[CURLOPT_URL] = $absUrl;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = $this->connectTimeout;
        $opts[CURLOPT_TIMEOUT] = $this->timeout;
        $opts[CURLOPT_HEADERFUNCTION] = $headerCallback;
        $opts[CURLOPT_HTTPHEADER] = $headers;
        if (!Confidences::getVerifySslCerts()) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
        }
        // @codingStandardsIgnoreStart
        // PSR2 requires all constants be upper case. Sadly, the CURL_SSLVERSION
        // constants to not abide by those rules.
        //
        // Opt into TLS 1.x support on older versions of curl. This causes some
        // curl versions, notably on RedHat, to upgrade the connection to TLS
        // 1.2, from the default TLS 1.0.
        if (!defined('CURL_SSLVERSION_TLSv1')) {
            define('CURL_SSLVERSION_TLSv1', 1); // constant not defined in PHP < 5.5
        }
        $opts[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1;
        // @codingStandardsIgnoreEnd

        curl_setopt_array($curl, $opts);
        $rbody = curl_exec($curl);

        if (!defined('CURLE_SSL_CACERT_BADFILE')) {
            define('CURLE_SSL_CACERT_BADFILE', 77);  // constant not defined in PHP
        }

        // @codeCoverageIgnoreStart
        $errno = curl_errno($curl);
        if ($errno == CURLE_SSL_CACERT
            || $errno == CURLE_SSL_PEER_CERTIFICATE
            || $errno == CURLE_SSL_CACERT_BADFILE
        ) {
            array_push(
                $headers,
                'X-Confidences-Client-Info: {"ca":"using Confidences-supplied CA bundle"}'
            );
            $cert = self::caBundle();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CAINFO, $cert);
            $rbody = curl_exec($curl);
        }

        if ($rbody === false) {
            $errno = curl_errno($curl);
            $message = curl_error($curl);
            curl_close($curl);
            $this->handleCurlError($absUrl, $errno, $message);
        }
        // @codeCoverageIgnoreEnd

        $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return [$rbody, $rcode, $rheaders];
    }

    /**
     * @param              $url
     * @param              $errno
     * @param              $message
     * @throws             Exception\ApiConnectionException
     * @codeCoverageIgnore
     */
    private function handleCurlError($url, $errno, $message) : void
    {
        switch ($errno) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $msg = "Could not connect to Confidences ($url).  Please check your "
                . "internet connection and try again.  If this problem persists,";
                break;
            case CURLE_SSL_CACERT:
            case CURLE_SSL_PEER_CERTIFICATE:
                $msg = "Could not verify Confidences's SSL certificate.  Please make sure "
                . "that your network is not intercepting certificates.  "
                . "(Try going to $url in your browser.)  "
                . "If this problem persists,";
                break;
            default:
                $msg = "Unexpected error communicating with Confidences.  "
                . "If this problem persists,";
        }
        $msg .= " let us know at support@confidences.co.";

        $msg .= "\n\n(Network error [errno $errno]: $message)";
        throw new Exception\ApiConnectionException($msg);
    }

    /**
     * @return string
     */
    private static function caBundle() : string
    {
        return dirname(__FILE__) . '/../../../data/ca-certificates.crt';
    }

    /**
     * @param array|mixed $arr    An map of param keys to values.
     * @param string|null $prefix
     *
     * Only public for testability, should not be called outside of CurlClient
     *
     * @return string A querystring, essentially.
     */
    public static function encode($arr, ?string $prefix = null)
    {
        if (!is_array($arr)) {
            return $arr;
        }

        $r = [];
        foreach ($arr as $k => $v) {
            if (is_null($v)) {
                continue;
            }

            if ($prefix) {
                if ($k !== null && (!is_int($k) || is_array($v))) {
                    $k = $prefix."[".$k."]";
                } else {
                    $k = $prefix."[]";
                }
            }

            if (is_array($v)) {
                $enc = self::encode($v, $k);
                if ($enc) {
                    $r[] = $enc;
                }
            } else {
                $r[] = urlencode($k)."=".urlencode($v);
            }
        }

        return implode("&", $r);
    }
}
