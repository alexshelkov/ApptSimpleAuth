<?php
namespace ApptSimpleAuth\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use ApptSimpleAuth\Service\Options\Acl as AclOptions;

use ApptSimpleAuth\Service\Exception\InvalidArgumentException;

class Acl implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        if ( isset($config['appt']['simple_auth']['acl']) && is_array($config['appt']['simple_auth']['acl']) ) {
            $options = $config['appt']['simple_auth']['acl'];
        } else {
            $options = array();
        }
        $options = new AclOptions($options);

        $class = $options->getClass();
        $name = $options->getName();

        if ( ! strlen($name) ) {
            throw new InvalidArgumentException("Acl name can't be empty");
        }

        if ( ! (class_exists($class) && is_subclass_of($class, 'SimpleAcl\Acl') ) ) {
            throw new InvalidArgumentException("$class is undefined or don't extends SimpleAcl\\Acl");
        }

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $odm */
        $odm =  $serviceLocator->get('doctrine.documentmanager.' . $options->getDocumentManager());

        $acl = $odm->find($class, $name);

        if ( is_null($acl) ) {
            $acl = new $class($name);
        }

        return $acl;
    }
}