<?php
return array(
    'console' => array(
        'router' => array(
            'routes' => array(
                'appt.simple_auth.user_add' => array(
                    'options' => array(
                        'route'    => 'aauth add user [--role=] <email> [<password>]',
                        'defaults' => array(
                            'controller' => 'ApptSimpleAuth\Zend\Controller\ConsoleUtils',
                            'action'     => 'userAdd'
                        )
                    )
                ),
                'appt.simple_auth.acl_add' => array(
                    'options' => array(
                        'route' => 'aauth add acl [<name>]',
                        'defaults' => array(
                            'controller' => 'ApptSimpleAuth\Zend\Controller\ConsoleUtils',
                            'action' => 'aclAdd'
                        )
                    )
                ),
                'appt.simple_auth.role_or_resource_add' => array(
                    'options' => array(
                        'route'    => 'aauth add (role|resource):what [--child=] <name>',
                        'defaults' => array(
                            'controller' => 'ApptSimpleAuth\Zend\Controller\ConsoleUtils',
                            'action'     => 'roleOrResourceAdd'
                        )
                    )
                ),
                'appt.simple_auth.rule_add' => array(
                    'options' => array(
                        'route'    => 'aauth add rule [--acl=] <role> <resource> <rule> [<allow>]',
                        'defaults' => array(
                            'controller' => 'ApptSimpleAuth\Zend\Controller\ConsoleUtils',
                            'action'     => 'ruleAdd'
                        )
                    )
                ),
                'appt.simple_auth.list' => array(
                    'options' => array(
                        'route' => 'aauth list (acl|user|role|resource|rule):what [--start=] [--count=]',
                        'defaults' => array(
                            'controller' => 'ApptSimpleAuth\Zend\Controller\ConsoleUtils',
                            'action' => 'list'
                        )
                    )
                ),
                'appt.simple_auth.remove' => array(
                    'options' => array(
                        'route' => 'aauth remove (acl|user|role|resource|rule):what [--use-rule-name=] <name>',
                        'defaults' => array(
                            'controller' => 'ApptSimpleAuth\Zend\Controller\ConsoleUtils',
                            'action' => 'remove'
                        )
                    )
                ),
                'appt.simple_auth.user_role_remove' => array(
                    'options' => array(
                        'route' => 'aauth remove user role <email> <role>',
                        'defaults' => array(
                            'controller' => 'ApptSimpleAuth\Zend\Controller\ConsoleUtils',
                            'action' => 'userRoleRemove'
                        )
                    )
                ),
                'appt.simple_auth.clear' => array(
                    'options' => array(
                        'route'    => 'aauth clear',
                        'defaults' => array(
                            'controller' => 'ApptSimpleAuth\Zend\Controller\ConsoleUtils',
                            'action'     => 'clear'
                        )
                    )
                ),
                'appt.simple_auth.allowed' => array(
                    'options' => array(
                        'route' => 'aauth allowed [-d|--details]:isDetails [--acl=] <role> <resource> <rule>',
                        'defaults' => array(
                            'controller' => 'ApptSimpleAuth\Zend\Controller\ConsoleUtils',
                            'action' => 'allowed'
                        )
                    )
                ),
            )
        )
    ),

    'router' => array(
        'routes' => array(
            'aauth' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => 'aauth/'
                ),
                'may_terminate' => false,
                'child_routes' => array(
                    'login' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'login/accept',
                            'defaults' => array(
                                'controller' => 'ApptSimpleAuth\Zend\Controller\Authentication',
                                'action' => 'login'
                            ),
                        ),
                    ),
                    'logout' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'logout/accept',
                            'defaults' => array(
                                'controller' => 'ApptSimpleAuth\Zend\Controller\Authentication',
                                'action' => 'logout'
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'doctrine' => array(
        'driver' => array(
            'appt_appt_simple_auth' => array(
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\YamlDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../data/doctrine/mongodb/mapping')
            ),
            'odm_default' => array(
                'drivers' => array(
                    'ApptSimpleAuth' => 'appt_appt_simple_auth',
                    'SimpleAcl' => 'appt_appt_simple_auth',
                )
            )
        ),
        'authentication' => array(
            'odm_default' => array(
                'identityClass' => 'ApptSimpleAuth\User',
                'identityProperty' => 'email',
                'credentialProperty' => 'password',
                'credentialCallable' => function ($user, $password) {
                    return $user->verifyPassword($password);
                },
            ),
        ),
    )
);