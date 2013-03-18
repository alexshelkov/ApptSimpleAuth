<?php
namespace ApptSimpleAuth\Exception;

use DomainException as SplException;
use ApptSimpleAuth\Exception\AccessViolationExceptionInterface;

class AccessViolationException extends SplException implements AccessViolationExceptionInterface
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @var int
     */
    protected $statusCode = 403;

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param string $url
     */
    public function setUri($url)
    {
        $this->uri = $url;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
}