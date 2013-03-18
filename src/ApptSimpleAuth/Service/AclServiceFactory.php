<?php
namespace ApptSimpleAuth\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use ApptSimpleAuth\Service\Options\Acl as AclOptions;
use ApptSimpleAuth\Service\Options\Auth as AuthOptions;

use ApptSimpleAuth\Service\Exception\RuntimeException;

use ApptSimpleAuth\AclService;

class AclServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $aclOptions = AclOptions::init($serviceLocator);
        $authOptions = AuthOptions::init($serviceLocator);

        $class = $aclOptions->getClass();
        $name = $aclOptions->getName();

        if ( ! strlen($name) ) {
            throw new RuntimeException('Acl name can\'t be empty');
        }

        if ( ! class_exists($class) ) {
            throw new RuntimeException("$class is undefined");
        }

        if (  ! is_subclass_of($class, 'ApptSimpleAuth\Acl') && $class != 'ApptSimpleAuth\Acl' ) {
            throw new RuntimeException("$class don't extends ApptSimpleAuth\\Acl");
        }

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $odm */
        $odm = $serviceLocator->get('doctrine.documentmanager.' . $authOptions->getDocumentManager());

        $aclService = new AclService($odm->getRepository($aclOptions->getClass()));

        $aclService->setDefaultAcl($name);

        return $aclService;
    }
}