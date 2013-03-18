<?php
namespace ApptSimpleAuth;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\FormElementProviderInterface;

use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;

use Zend\Console\Adapter\AdapterInterface;

use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface,
    ConsoleUsageProviderInterface,
    ControllerProviderInterface,
    ControllerPluginProviderInterface,
    ViewHelperProviderInterface,
    BootstrapListenerInterface,
    FormElementProviderInterface
{
    public function getFormElementConfig()
    {
        return array(
            'shared' => array(
                'appt.simple_auth.form.logout' => true,
                'appt.simple_auth.form.login' => true,
                'appt.simple_auth.form.fieldset.user' => true
            ),
            'factories' => array(
                'appt.simple_auth.form.logout' => 'ApptSimpleAuth\Service\Zend\Form\LogoutFactory',
                'appt.simple_auth.form.login' => 'ApptSimpleAuth\Service\Zend\Form\LoginFactory',
                'appt.simple_auth.form.fieldset.user' => 'ApptSimpleAuth\Service\Zend\Form\Fieldset\UserFactory'
            )
        );
    }

    public function onBootstrap(EventInterface $event)
    {
        /** @var \Zend\Mvc\MvcEvent $event */
        $application = $event->getApplication();

        $listeners = $application->getServiceManager()->get('appt.simple_auth.event_listener');

        $em = $application->getEventManager();
        $em->attachAggregate($listeners);

        $sem = $em->getSharedManager();
        $sem->attachAggregate($listeners);
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'auth' => 'ApptSimpleAuth\Service\Zend\Controller\Plugin\AuthFactory',
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerConfig()
    {
        return array(
            'invokables' => array(
                'ApptSimpleAuth\Zend\Controller\ConsoleUtils' => 'ApptSimpleAuth\Zend\Controller\ConsoleUtilsController',
                'ApptSimpleAuth\Zend\Controller\Authentication' => 'ApptSimpleAuth\Zend\Controller\AuthenticationController',
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'auth' => 'ApptSimpleAuth\Service\Zend\View\Helper\AuthFactory',
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            AutoloaderFactory::STANDARD_AUTOLOADER => array(
                StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'appt.simple_auth.acl' => 'ApptSimpleAuth\Service\AclServiceFactory',
                'appt.simple_auth.manager' => 'ApptSimpleAuth\Service\ManagerServiceFactory',
                'appt.simple_auth.auth' => 'ApptSimpleAuth\Service\AuthServiceFactory',
                'appt.simple_auth.forms' => 'ApptSimpleAuth\Service\FormsServiceFactory',
                'appt.simple_auth.event_listener' => 'ApptSimpleAuth\Service\EventListenerFactory'
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(AdapterInterface $console) {
        return array(
            __NAMESPACE__ . ' usage:',
            'aauth add acl <name>' => 'Add acl',
            'aauth add user [--role=] <email> [<password>]' => 'Add user',
            'aauth add (role|resource) [--child=] <name>' => 'Add role or resource',
            'aauth add rule [--acl=] <role> <resource> <rule> [<allow>]' => 'Add rule',
            'aauth list (acl|user|role|resource|rule) [--start=] [--count=]' => 'List objects',
            'aauth remove [--use-rule-name=] (acl|user|role|resource|rule)' => 'Remove object',
            'aauth remove user role <email> <role>' => 'Remove users role',
            'aauth clear' => 'Clear acls, users, roles, resources and rules',
            'aauth allowed [-d|--details]:isDetails [--acl=] <role> <resource> <rule>' => 'Check access'
        );
    }
}