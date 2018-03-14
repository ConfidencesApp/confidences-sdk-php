<?php
namespace Confidences;

class Confidences
{
    const VERSION = '1.0.1';
    
    // @var string The Confidences API key to be used for requests.
    public static $apiKey;
    
    // @var string The base URL for the Confidences API.
    public static $apiBase = 'https://api.confidences.co/api';

    // @var boolean Defaults to true.
    public static $verifySslCerts = true;

    // @var array The application's information (name, version, URL)
    public static $appInfo = null;
    
    /**
     * @return string $apiKey
     */
    public static function getApiKey()
    {
        return self::$apiKey;
    }
    
    /**
     * @param string $apiKey
     */
    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }
    
    /**
     * @return string $apiBase
     */
    public static function getApiBase()
    {
        return self::$apiBase;
    }
    
    /**
     * @param string $apiBase
     */
    public static function setApiBase($apiBase)
    {
        self::$apiBase = $apiBase;
    }
    
    /**
     * @return boolean $verifySslCerts
     */
    public static function getVerifySslCerts()
    {
        return self::$verifySslCerts;
    }
    
    /**
     * @param boolean $verifySslCerts
     */
    public static function setVerifySslCerts($verifySslCerts)
    {
        self::$verifySslCerts = $verifySslCerts;
    }

    /**
     * @return array | null The application's information
     */
    public static function getAppInfo()
    {
        return self::$appInfo;
    }
    
    /**
     * @param string $appName The application's name
     * @param string $appVersion The application's version
     * @param string $appUrl The application's URL
     */
    public static function setAppInfo($appName, $appVersion = null, $appUrl = null)
    {
        if (self::$appInfo === null) {
            self::$appInfo = array();
        }
        self::$appInfo['name'] = $appName;
        self::$appInfo['version'] = $appVersion;
        self::$appInfo['url'] = $appUrl;
    }
}
