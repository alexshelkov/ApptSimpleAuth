<?php
namespace ApptSimpleAuth\Service\Zend\Form\FieldSet;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use ApptSimpleAuth\Zend\Form\Fieldset\User;

use Zend\Form\FormElementManager;

use ApptSimpleAuth\Service\Exception\InvalidArgumentException;

class UserFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if ( ! $serviceLocator instanceof FormElementManager ) {
            throw new InvalidArgumentException('Except instance of Zend\Form\FormElementManager got ' . get_class($serviceLocator));
        }

        /** @var HelperPluginManager $serviceLocator */
        $sm = $serviceLocator->getServiceLocator();

        $fieldSet = new User(null, array('auth' => $sm->get('appt.simple_auth.auth')));

        return $fieldSet;
    }
}