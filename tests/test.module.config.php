<?php
return array(
    'appt' => array(
        'simple_auth' => array(
            'acl' => array(
                'name' => 'Test',
                'class' => 'ApptSimpleAuth\Acl',
            ),
            'documentManager' => 'odm_default'
        ),
    ),

    'doctrine' => array(
        'configuration' => array(
            'odm_default' => array(
                'metadata_cache'     => 'array',

                'driver'             => 'odm_default',

                'generate_proxies'   => true,
                'proxy_dir'          => __DIR__ . '/../cache/doctrine/proxy',
                'proxy_namespace'    => 'ApptSimpleAuth\Proxy',

                'generate_hydrators' => true,
                'hydrator_dir'       => __DIR__ . '/../cache/doctrine/hydrator',
                'hydrator_namespace' => 'ApptSimpleAuth\Hydrator',

                'default_db'         => 'doctrineMongoODMModuleTest',
            )
        ),
    )
);
