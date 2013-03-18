<?php
namespace ApptSimpleAuth\Service\Exception;

use RuntimeException as SplException;
use ApptSimpleAuth\Service\Exception\ExceptionInterface as Exception;

class RuntimeException extends SplException implements Exception
{

}
