<?php
namespace ApptSimpleAuth\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use ApptSimpleAuth\Service\Options\Auth as Options;

use ApptSimpleAuth\ManagerService;

class ManagerServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ManagerService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options = Options::init($serviceLocator);

        $odm =  $serviceLocator->get('doctrine.documentmanager.' . $options->getDocumentManager());
        $aclService = $serviceLocator->get('appt.simple_auth.acl');

        $manager = new ManagerService($odm, $aclService);

        return $manager;
    }
}