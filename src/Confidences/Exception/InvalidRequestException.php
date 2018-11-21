<?php
namespace Confidences\Exception;

class InvalidRequestException extends BaseException
{
    /**
     * @var string|null
     */
    protected $confidencesParam;

    /**
     * InvalidRequestException constructor.
     *
     * @param string      $message
     * @param string      $confidencesParam
     * @param int|null    $httpStatus
     * @param null|string $httpBody
     * @param mixed       $jsonBody
     * @param array|null  $httpHeaders
     */
    public function __construct(
        string $message,
        ?string $confidencesParam = null,
        ?int $httpStatus = null,
        ?string $httpBody = null,
        $jsonBody = null,
        ?array $httpHeaders = null
    ) {
        parent::__construct($message, $httpStatus, $httpBody, $jsonBody, $httpHeaders);
        $this->confidencesParam = $confidencesParam;
    }

    /**
     * @return string|null
     */
    public function getConfidencesParam() : ?string
    {
        return $this->confidencesParam;
    }
}
