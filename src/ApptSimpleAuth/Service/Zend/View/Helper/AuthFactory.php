<?php
namespace ApptSimpleAuth\Service\Zend\View\Helper;

use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\View\HelperPluginManager;
use ApptSimpleAuth\Service\Exception\InvalidArgumentException;

use ApptSimpleAuth\Service\Zend\Controller\Plugin\AuthFactory as BaseFactory;

class AuthFactory extends BaseFactory
{
    protected $class = 'ApptSimpleAuth\Zend\View\Helper\Auth';

    protected function checkServiceLocatorInstance(ServiceLocatorInterface $serviceLocator)
    {
        if ( ! $serviceLocator instanceof HelperPluginManager ) {
            throw new InvalidArgumentException('Except instance of Zend\View\HelperPluginManager got ' . get_class($serviceLocator));
        }
    }
}