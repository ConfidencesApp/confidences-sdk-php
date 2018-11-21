<?php
namespace Confidences;

class Confidences
{
    const VERSION = '1.0.2';

    /**
     * @var string|null The Confidences API key to be used for requests.
     */
    public static $apiKey = null;

    /**
     * @var string The base URL for the Confidences API.
     */
    public static $apiBase = 'https://api.confidences.co/api';

    /**
     * @var bool Defaults to true.
     */
    public static $verifySslCerts = true;

    /**
     * @var array|null The application's information (name, version, URL)
     */
    public static $appInfo = null;
    
    /**
     * @return string|null $apiKey
     */
    public static function getApiKey() : ?string
    {
        return self::$apiKey;
    }
    
    /**
     * @param string $apiKey
     */
    public static function setApiKey($apiKey) : void
    {
        self::$apiKey = $apiKey;
    }
    
    /**
     * @return string|null $apiBase
     */
    public static function getApiBase() : ?string
    {
        return self::$apiBase;
    }
    
    /**
     * @param string $apiBase
     */
    public static function setApiBase($apiBase) : void
    {
        self::$apiBase = $apiBase;
    }
    
    /**
     * @return boolean $verifySslCerts
     */
    public static function getVerifySslCerts() : bool
    {
        return self::$verifySslCerts;
    }
    
    /**
     * @param boolean $verifySslCerts
     */
    public static function setVerifySslCerts($verifySslCerts) : void
    {
        self::$verifySslCerts = $verifySslCerts;
    }

    /**
     * @return array|null The application's information
     */
    public static function getAppInfo() : ?array
    {
        return self::$appInfo;
    }
    
    /**
     * @param string $appName    The application's name
     * @param string $appVersion The application's version
     * @param string $appUrl     The application's URL
     */
    public static function setAppInfo(string $appName, ?string $appVersion = null, ?string $appUrl = null) : void
    {
        if (self::$appInfo === null) {
            self::$appInfo = [];
        }
        self::$appInfo['name'] = $appName;
        self::$appInfo['version'] = $appVersion;
        self::$appInfo['url'] = $appUrl;
    }
}
