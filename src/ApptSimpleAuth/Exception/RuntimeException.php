<?php
namespace ApptSimpleAuth\Exception;

use RuntimeException as SplException;
use ApptSimpleAuth\Exception\ExceptionInterface as Exception;

class RuntimeException extends SplException implements Exception
{
}