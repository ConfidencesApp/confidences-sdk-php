<?php
namespace Confidences\Exception;

class InvalidRequestException extends BaseException
{
    public function __construct(
        $message,
        $confidencesParam,
        $httpStatus = null,
        $httpBody = null,
        $jsonBody = null,
        $httpHeaders = null
    ) {
        parent::__construct($message, $httpStatus, $httpBody, $jsonBody, $httpHeaders);
        $this->confidencesParam = $confidencesParam;
    }

    public function getConfidencesParam()
    {
        return $this->confidencesParam;
    }
}
