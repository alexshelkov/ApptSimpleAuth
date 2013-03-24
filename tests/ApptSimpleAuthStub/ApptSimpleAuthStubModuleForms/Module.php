<?php
namespace ApptSimpleAuthStubModuleForms;

class Module
{
    static public $config = array();

    public function getConfig()
    {
        return array(
            'appt' => array(
                'simple_auth' => self::$config
            ),
            'router' => array(
                'routes' => array(
                    'test_redirect' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'testLogoutRedirectToRoute',
                            'defaults' => array(
                                'controller' => 'ApptSimpleAuthStubModuleForms\Controller\Test',
                                'action' => 'testLogoutRedirectToRoute',
                            )
                        ),
                    ),
                    'login' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'login',
                            'defaults' => array(
                                'controller' => 'ApptSimpleAuthStubModuleForms\Controller\Test',
                                'action' => 'login',
                            )
                        ),
                    ),
                    'logout' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'logout',
                            'defaults' => array(
                                'controller' => 'ApptSimpleAuthStubModuleForms\Controller\Test',
                                'action' => 'logout',
                            )
                        ),
                    ),
                )
            ),
            'view_manager' => array(
                'template_map' => array(
                    '404' => __DIR__ . '/view/appt-simple-auth-stub-module-forms/test/404.phtml',
                    'form/aauth/logout' => __DIR__ . '/view/appt-simple-auth-stub-module-forms/test/logout-form.phtml',
                    'form/aauth/login' => __DIR__ . '/view/appt-simple-auth-stub-module-forms/test/login-form.phtml',
                    'layout/layout' => __DIR__ . '/view/appt-simple-auth-stub-module-forms/test/layout.phtml',
                ),
                'template_path_stack' => array(
                    __DIR__ . '/view/'
                ),
            ),
            'controllers' => array(
                'invokables' => array(
                    'ApptSimpleAuthStubModuleForms\Controller\Test' => 'ApptSimpleAuthStubModuleForms\Controller\TestController',
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