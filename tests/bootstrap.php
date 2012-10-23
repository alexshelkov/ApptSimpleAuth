<?php
$applicationRoot = __DIR__ . '/../';

chdir($applicationRoot);

// Init composer autoloaders
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('ApptSimpleAuthTest\\', __DIR__ );
$loader->add('DoctrineMongoODMModuleTest',  '/Users/san/Sites/myprojects/ApptSimpleAuth/vendor/doctrine/doctrine-mongo-odm-module/tests');

$config = include(__DIR__ . '/test.application.config.php');

\DoctrineMongoODMModuleTest\AbstractTest::setApplicationConfig($config);