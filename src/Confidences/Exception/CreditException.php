<?php

namespace Confidences\Exception;

class CreditException extends BaseException
{
    public function __construct(
        $message,
        $confidencesParam,
        $confidencesCode,
        $httpStatus,
        $httpBody,
        $jsonBody,
        $httpHeaders = null
    ) {
        parent::__construct($message, $httpStatus, $httpBody, $jsonBody, $httpHeaders);
        $this->confidencesParam = $confidencesParam;
        $this->confidencesCode = $confidencesCode;
    }
    
    /**
     * @codeCoverageIgnore
     */
    public function getConfidencesCode()
    {
        return $this->confidencesCode;
    }
    
    /**
     * @codeCoverageIgnore
     */
    public function getConfidencesParam()
    {
        return $this->confidencesParam;
    }
}
