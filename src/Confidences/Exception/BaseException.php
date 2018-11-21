<?php
namespace Confidences\Exception;

use Exception;

abstract class BaseException extends Exception
{
    /**
     * @var int|null
     */
    protected $httpStatus;

    /**
     * @var null|string
     */
    protected $httpBody;

    /**
     * @var mixed
     */
    protected $jsonBody;

    /**
     * @var array|null
     */
    protected $httpHeaders;

    /**
     * @var string|null
     */
    protected $requestId;

    /**
     * BaseException constructor.
     *
     * @param string      $message
     * @param int|null    $httpStatus
     * @param null|string $httpBody
     * @param mixed       $jsonBody
     * @param array|null  $httpHeaders
     */
    public function __construct(
        string $message,
        ?int $httpStatus = null,
        ?string $httpBody = null,
        $jsonBody = null,
        ?array $httpHeaders = null
    ) {
        parent::__construct($message);
        $this->httpStatus = $httpStatus;
        $this->httpBody = $httpBody;
        $this->jsonBody = $jsonBody;
        $this->httpHeaders = $httpHeaders;
        $this->requestId = null;

        if ($httpHeaders && isset($httpHeaders['Request-Id'])) {
            $this->requestId = $httpHeaders['Request-Id'];
        }
    }

    /**
     * @return int|null
     */
    public function getHttpStatus() : ?int
    {
        return $this->httpStatus;
    }

    /**
     * @return null|string
     */
    public function getHttpBody() : ?string
    {
        return $this->httpBody;
    }

    /**
     * @return mixed|null
     */
    public function getJsonBody()
    {
        return $this->jsonBody;
    }

    /**
     * @return array|null
     */
    public function getHttpHeaders() : ?array
    {
        return $this->httpHeaders;
    }

    /**
     * @return null|string
     */
    public function getRequestId() : ?string
    {
        return $this->requestId;
    }

    /**
     * @return             string
     * @codeCoverageIgnore
     */
    public function __toString() : string
    {
        $id = $this->requestId ? " from API request '{$this->requestId}'": "";
        $message = explode("\n", parent::__toString());
        $message[0] .= $id;
        return implode("\n", $message);
    }
}
