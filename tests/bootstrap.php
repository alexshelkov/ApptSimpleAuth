<?php
$applicationRoot = __DIR__ . '/../';

chdir($applicationRoot);

exec('rm -Rf cache');
exec('mkdir -p cache/doctrine/proxy cache/doctrine/hydrator');
exec('chmod -R a+w cache');

// Init composer autoloaders
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('ApptSimpleAuthTest\\', __DIR__ );
$loader->add('DoctrineMongoODMModuleTest',  __DIR__  . '/../vendor/doctrine/doctrine-mongo-odm-module/tests');

$config = include(__DIR__ . '/test.application.config.php');

\DoctrineMongoODMModuleTest\AbstractTest::setApplicationConfig($config);