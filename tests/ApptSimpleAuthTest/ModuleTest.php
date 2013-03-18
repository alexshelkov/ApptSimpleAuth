<?php
namespace ApptSimpleAuthTest;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

use ApptSimpleAuth\AuthService;
use ApptSimpleAuth\ManagerService;

use Doctrine\ODM\MongoDB\DocumentManager;

class ModuleTest extends AbstractConsoleControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(include TEST_APPLICATION_CONFIG);
        parent::setUp();
    }

    public function testModuleLoaded()
    {
        $this->dispatch('');

        $this->assertModulesLoaded(array(
            'DoctrineModule',
            'DoctrineMongoODMModule',
            'ApptSimpleAuth'
        ));

        $this->assertConsoleOutputContains('ApptSimpleAuth usage:');
    }
}
