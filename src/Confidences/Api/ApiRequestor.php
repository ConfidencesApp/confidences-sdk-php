<?php

namespace Confidences\Api;

use Confidences\Confidences;
use Confidences\Exception;
use Confidences\HttpClient;
use Confidences\Util;

class ApiRequestor
{
    private $_apiKey;

    private $_apiBase;

    private static $_httpClient;

    public function __construct($apiKey = null, $apiBase = null)
    {
        $this->_apiKey = $apiKey;
        if (!$apiBase) {
            $apiBase = Confidences::$apiBase;
        }
        $this->_apiBase = $apiBase;
    }

    private static function _encodeObjects($d)
    {
        if ($d === true) {
            return 'true';
        } elseif ($d === false) {
            return 'false';
        } elseif (is_array($d)) {
            $res = [];
            foreach ($d as $k => $v) {
                $res[$k] = self::_encodeObjects($v);
            }
            return $res;
        } else {
            return Util\Util::utf8($d);
        }
    }

    /**
     * @param string     $method
     * @param string     $url
     * @param array|null $params
     * @param array|null $headers
     *
     * @return array An array whose first element is an API response and second
     *    element is the API key used to make the request.
     */
    public function request($method, $url, $params = null, $headers = null)
    {
        if (!$params) {
            $params = [];
        }
        if (!$headers) {
            $headers = [];
        }
        list($rbody, $rcode, $rheaders) = $this->_requestRaw($method, $url, $params, $headers);
        $json = $this->_interpretResponse($rbody, $rcode, $rheaders);
        return new ApiResponse($rbody, $rcode, $rheaders, $json);
    }

    /**
     * @param string $rbody    A JSON string.
     * @param int    $rcode
     * @param array  $rheaders
     * @param array  $resp
     *
     * @throws Exception\InvalidRequestException if the error is caused by the user.
     * @throws Exception\AuthenticationException if the error is caused by a lack of
     *    permissions.
     * @throws Exception\AuthorizationException if the error is caused by a lack of
     *    permissions.
     * @throws Exception\ApiException otherwise.
     */
    public function handleApiError($rbody, $rcode, $rheaders, $resp)
    {
        if (!is_array($resp) || !isset($resp['error'])) {
            $msg = "Invalid response object from API: $rbody "
              . "(HTTP response code was $rcode)";
            throw new Exception\ApiException($msg, $rcode, $rbody, $resp, $rheaders);
        }

        $error = $resp['error'];
        $msg = isset($error['message']) ? $error['message'] : null;
        $param = isset($error['param']) ? $error['param'] : null;
        $code = isset($error['code']) ? $error['code'] : null;

        switch ($rcode) {
            case 400:
            case 404:
                throw new Exception\InvalidRequestException($msg, $param, $rcode, $rbody, $resp, $rheaders);
            case 401:
                throw new Exception\AuthenticationException($msg, $rcode, $rbody, $resp, $rheaders);
            case 402:
                throw new Exception\CreditException($msg, $param, $code, $rcode, $rbody, $resp, $rheaders);
            case 403:
                throw new Exception\AuthorizationException($msg, $rcode, $rbody, $resp, $rheaders);
            case 422:
                throw new Exception\UniqueResponseException($msg, $param, $rcode, $rbody, $resp, $rheaders);
            default:
                throw new Exception\ApiException($msg, $rcode, $rbody, $resp, $rheaders);
        }
    }

    private static function _formatAppInfo($appInfo)
    {
        if ($appInfo !== null) {
            $string = $appInfo['name'];
            if ($appInfo['version'] !== null) {
                $string .= '/' . $appInfo['version'];
            }
            if ($appInfo['url'] !== null) {
                $string .= ' (' . $appInfo['url'] . ')';
            }
            return $string;
        } else {
            return null; // @codeCoverageIgnore
        }
    }

    private static function _defaultHeaders($apiKey)
    {
        $uaString = 'Confidences/v1 PhpBindings/' . Confidences::VERSION;

        $langVersion = phpversion();
        $uname = php_uname();
        $curlVersion = curl_version();
        $appInfo = Confidences::getAppInfo();
        $ua = array(
            'bindings_version' => Confidences::VERSION,
            'lang' => 'php',
            'lang_version' => $langVersion,
            'publisher' => 'confidences',
            'uname' => $uname,
            'httplib' => 'curl ' . $curlVersion['version'],
            'ssllib' => $curlVersion['ssl_version'],
        );
        if ($appInfo !== null) {
            $uaString .= ' ' . self::_formatAppInfo($appInfo);
            $ua['application'] = $appInfo;
        }

        $defaultHeaders = array(
            'X-Confidences-Client-User-Agent' => json_encode($ua),
            'User-Agent' => $uaString,
            'Authorization' => 'Bearer ' . $apiKey,
        );
        return $defaultHeaders;
    }

    private function _requestRaw($method, $url, $params, $headers)
    {
        $myApiKey = $this->_apiKey;
        if (!$myApiKey) {
            $myApiKey = Confidences::$apiKey;
        }

        if (!$myApiKey) {
            $msg = 'No API key provided.  (HINT: set your API key using '
                . '"Confidences::setApiKey(<API-KEY>)".  You can generate API keys from '
                    . 'the Confidences web interface.  See https://confidences.com/team/api for '
                        . 'details, or email support@confidences.co if you have any questions.';
            throw new Exception\AuthenticationException($msg);
        }

        $absUrl = $this->_apiBase.$url;
        $params = self::_encodeObjects($params);
        $defaultHeaders = $this->_defaultHeaders($myApiKey);

        $hasFile = false;
        $hasCurlFile = class_exists('\CURLFile', false);
        foreach ($params as $k => $v) {
            if (is_resource($v)) {
                $hasFile = true;
                $params[$k] = self::_processResourceParam($v, $hasCurlFile);
            } elseif ($hasCurlFile && $v instanceof \CURLFile) {
                $hasFile = true;
            }
        }

        if ($hasFile) {
            $defaultHeaders['Content-Type'] = 'multipart/form-data';
        } else {
            $defaultHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $combinedHeaders = array_merge($defaultHeaders, $headers);
        $rawHeaders = [];

        foreach ($combinedHeaders as $header => $value) {
            $rawHeaders[] = $header . ': ' . $value;
        }

        list($rbody, $rcode, $rheaders) = $this->httpClient()->request(
            $method,
            $absUrl,
            $rawHeaders,
            $params,
            $hasFile
        );
        return [$rbody, $rcode, $rheaders];
    }

    /**
     * @codeCoverageIgnore
     */
    private function _processResourceParam($resource, $hasCurlFile)
    {
        if (get_resource_type($resource) !== 'stream') {
            throw new Exception\ApiException(
                'Attempted to upload a resource that is not a stream'
            );
        }

        $metaData = stream_get_meta_data($resource);
        if ($metaData['wrapper_type'] !== 'plainfile') {
            throw new Exception\ApiException(
                'Only plainfile resource streams are supported'
            );
        }

        if ($hasCurlFile) {
            // We don't have the filename or mimetype, but the API doesn't care
            return new \CURLFile($metaData['uri']);
        } else {
            return '@'.$metaData['uri'];
        }
    }

    private function _interpretResponse($rbody, $rcode, $rheaders)
    {
        try {
            $resp = json_decode($rbody, true);
        } catch (\Exception $e) {
            // @codeCoverageIgnoreStart
            $msg = "Invalid response body from API: $rbody "
              . "(HTTP response code was $rcode)";
            throw new Exception\ApiException($msg, $rcode, $rbody);
            // @codeCoverageIgnoreEnd
        }

        if ($rcode < 200 || $rcode >= 300) {
            $this->handleApiError($rbody, $rcode, $rheaders, $resp);
        }
        return $resp;
    }

    public static function setHttpClient($client)
    {
        self::$_httpClient = $client;
    }

    private function httpClient()
    {
        if (!self::$_httpClient) {
            self::$_httpClient = HttpClient\CurlClient::instance(); // @codeCoverageIgnore
        }
        return self::$_httpClient;
    }
}
