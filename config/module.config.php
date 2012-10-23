<?php
return array(
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
                'credentialProperty' => 'password'
            ),
        ),
    )
);