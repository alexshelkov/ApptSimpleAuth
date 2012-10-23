<?php
namespace ApptSimpleAuth\Service\Exception;

use InvalidArgumentException as SplInvalidArgumentException;
use ApptSimpleAuth\Service\Exception\Exception;

class InvalidArgumentException extends SplInvalidArgumentException implements Exception
{

}
