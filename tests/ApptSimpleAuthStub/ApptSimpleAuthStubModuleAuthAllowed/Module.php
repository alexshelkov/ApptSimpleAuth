<?php
namespace ApptSimpleAuthStubModuleAuthAllowed;

use ApptSimpleAuth\Feature\AuthControlInterface;

use ApptSimpleAuth\AuthService;
use Zend\Mvc\Router\RouteMatch;

class Module implements AuthControlInterface
{
    static public $allowed;
    static public $config = array();

    public function isAuthDenied(AuthService $auth, RouteMatch $routeMatch)
    {
        $allowed = self::$allowed;
        return $allowed();
    }

    public function getConfig()
    {
        return array(
            'appt' => array(
                'simple_auth' => self::$config
            ),
            'router' => array(
                'routes' => array(
                    'test' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'testModuleAuth',
                            'defaults' => array(
                                'controller' => 'ApptSimpleAuthStubModuleAuthAllowed\Controller\Test',
                                'action' => 'index',
                            )
                        ),
                    ),
                )
            ),
            'view_manager' => array(
                'template_map' => array(
                    'custom/auth/error' => __DIR__ . '/view/appt-simple-auth-stub-module-auth-allowed/test/custom_auth_error.phtml',
                    'error' => __DIR__ . '/view/appt-simple-auth-stub-module-auth-allowed/test/error.phtml',
                    'layout/layout' => __DIR__ . '/view/appt-simple-auth-stub-module-auth-allowed/test/layout.phtml',
                ),
                'template_path_stack' => array(
                    __DIR__ . '/view/'
                ),
            ),
            'controllers' => array(
                'invokables' => array(
                    'ApptSimpleAuthStubModuleAuthAllowed\Controller\Test' => 'ApptSimpleAuthStubModuleAuthAllowed\Controller\TestController',
                ),
            ),
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}