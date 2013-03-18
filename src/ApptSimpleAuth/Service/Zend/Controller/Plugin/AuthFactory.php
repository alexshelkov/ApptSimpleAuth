<?php
namespace ApptSimpleAuth\Service\Zend\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Mvc\Controller\PluginManager;

use ApptSimpleAuth\Service\Exception\InvalidArgumentException;

class AuthFactory implements FactoryInterface
{
    protected $class = 'ApptSimpleAuth\Zend\Controller\Plugin\Auth';

    protected function checkServiceLocatorInstance(ServiceLocatorInterface $serviceLocator)
    {
        if ( ! $serviceLocator instanceof PluginManager ) {
            throw new InvalidArgumentException('Except instance of Zend\Mvc\Controller\PluginManager got ' . get_class($serviceLocator));
        }
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->checkServiceLocatorInstance($serviceLocator);

        /** @var PluginManager $serviceLocator */
        $sm = $serviceLocator->getServiceLocator();

        $auth = $sm->get('appt.simple_auth.auth');
        $loginForm = $sm->get('FormElementManager')->get('appt.simple_auth.form.login');
        $logoutForm = $sm->get('FormElementManager')->get('appt.simple_auth.form.logout');

        $class = $this->class;
        $authPlugin = new $class($auth, $loginForm, $logoutForm);

        return $authPlugin;
    }
}