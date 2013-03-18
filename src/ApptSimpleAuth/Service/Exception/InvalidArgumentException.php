<?php
namespace ApptSimpleAuth\Service\Exception;

use InvalidArgumentException as SplException;
use ApptSimpleAuth\Service\Exception\ExceptionInterface as Exception;

class InvalidArgumentException extends SplException implements Exception
{

}
