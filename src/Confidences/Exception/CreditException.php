<?php

namespace Confidences\Exception;

class CreditException extends BaseException
{
    /**
     * @var string
     */
    protected $confidencesParam;

    /**
     * @var string
     */
    protected $confidencesCode;

    /**
     * CreditException constructor.
     * @param string $message
     * @param string|null $confidencesParam
     * @param string $confidencesCode
     * @param int $httpStatus
     * @param string $httpBody
     * @param mixed $jsonBody
     * @param array|null $httpHeaders
     */
    public function __construct(
        string $message,
        ?string $confidencesParam = null,
        string $confidencesCode,
        int $httpStatus,
        string $httpBody,
        $jsonBody = null,
        ?array $httpHeaders = null
    ) {
        parent::__construct($message, $httpStatus, $httpBody, $jsonBody, $httpHeaders);
        $this->confidencesParam = $confidencesParam;
        $this->confidencesCode = $confidencesCode;
    }

    /**
     * @return string
     */
    public function getConfidencesCode() : string
    {
        return $this->confidencesCode;
    }

    /**
     * @return string|null
     */
    public function getConfidencesParam() : ?string
    {
        return $this->confidencesParam;
    }
}
