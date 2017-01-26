<?php
namespace Confidences;

use Confidences\Api\ApiRequestor;
use Confidences\Exception\BaseException;

class Survey
{
    private static $exception;
    
    /**
    * Send the campaign to the specified recipient with optionnal extra data
    *
    * @param  string $campaignToken
    * @param  string $recipient
    * @param  array  $data
    * @return boolean Returns true for a successful sending, false otherwise.
    */
    public static function share($campaignToken, $recipient, $data = [])
    {
        self::$exception = null;
        
        if (!is_string($campaignToken) || empty($campaignToken)) {
            throw new \InvalidArgumentException('Survey campaign not setted.');
        }
        
        if (!is_string($recipient) || empty($recipient)) {
            throw new \InvalidArgumentException('$recipient must be a string (ISO mobile phone or email address)');
        }

        $params = [
            'recipient' => $recipient,
            'campaign_token' => $campaignToken,
            'merge_map' => $data
        ];
        
        try {
            $requestor = new ApiRequestor(Confidences::getApiKey(), Confidences::getApiBase());
            $response = $requestor->request('post', '/survey/share', $params);
            
            return $response->code == 200
            && isset($response->json['data']['result'])
            && $response->json['data']['result'] == 'sent';
        } catch (BaseException $e) {
            self::$exception = $e;
        }
        
        return false;
    }
    
    /**
     * @return BaseException $exception
     */
    public static function getException()
    {
        return self::$exception;
    }
}
