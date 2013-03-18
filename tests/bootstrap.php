<?php
$applicationRoot = __DIR__ . '/../';

chdir($applicationRoot);

exec('rm -Rf cache');
exec('mkdir -p cache/doctrine/proxy cache/doctrine/hydrator');
exec('chmod -R a+w cache');

define('TEST_APPLICATION_CONFIG', __DIR__ . '/test.application.config.php');
define('TEST_STUB', __DIR__ . '/ApptSimpleAuthStub');

// Init composer autoloaders
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('ApptSimpleAuthTest', __DIR__ );