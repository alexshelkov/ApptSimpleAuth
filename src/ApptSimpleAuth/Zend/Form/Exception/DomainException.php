<?php
namespace ApptSimpleAuth\Zend\Form\Exception;

use DomainException as SplException;
use ApptSimpleAuth\Zend\Form\Exception\ExceptionInterface as Exception;

class DomainException extends SplException implements Exception
{
}
