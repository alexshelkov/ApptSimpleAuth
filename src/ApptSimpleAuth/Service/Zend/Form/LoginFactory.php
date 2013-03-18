<?php
namespace ApptSimpleAuth\Service\Zend\Form;

use Zend\ServiceManager\ServiceLocatorInterface;

use ApptSimpleAuth\Service\Zend\Form\LogoutFactory;

use ApptSimpleAuth\Service\Options\Forms as Options;
use Zend\Http\Request as HttpRequest;

class LoginFactory extends LogoutFactory
{
    protected $formClass = 'ApptSimpleAuth\Zend\Form\Login';

    protected function addDependencies(Options $serviceOptions, array $options, ServiceLocatorInterface $serviceLocator)
    {
        $options = array();
        $options = parent::addDependencies($serviceOptions, $options, $serviceLocator);

        $request = $serviceLocator->get('Request');

        if ( $request instanceof HttpRequest ) {
            $options['request'] = $request;
        }

        return $options;
    }
}