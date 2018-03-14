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

    public function getConfidencesCode()
    {
        return $this->confidencesCode;
    }
    
    public function getConfidencesParam()
    {
        return $this->confidencesParam;
    }
}
