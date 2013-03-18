<?php
namespace ApptSimpleAuth\Exception;

use ApptSimpleAuth\Exception\ExceptionInterface;

interface AccessViolationExceptionInterface extends ExceptionInterface
{
    public function getUri();

    public function getStatusCode();
}
