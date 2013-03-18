<?php
namespace ApptSimpleAuth\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ApptSimpleAuth\Service\Options\Auth as AuthOptions;

use ApptSimpleAuth\EventListener;

class EventListenerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return EventListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authOptions = AuthOptions::init($serviceLocator);

        $auth = new EventListener();

        $auth->setErrorTemplate($authOptions->getErrorTemplate());

        return $auth;
    }
}