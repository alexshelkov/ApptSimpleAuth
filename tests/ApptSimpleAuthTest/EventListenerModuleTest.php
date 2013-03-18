<?php
namespace ApptSimpleAuthTest;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use ApptSimpleAuthStubModuleAuthAllowed\Module;
use ApptSimpleAuthStubModuleAuthAllowed\Controller\TestController;
use ApptSimpleAuth\Exception\AccessViolationException;

class EventListenerModuleTest extends AbstractHttpControllerTestCase
{
    protected $traceError = false;

    public function setUp()
    {
        $config = include TEST_APPLICATION_CONFIG;
        $config['module_listener_options']['module_paths']['ApptSimpleAuthStubModuleAuthAllowed'] =
                'tests/ApptSimpleAuthStub/ApptSimpleAuthStubModuleAuthAllowed';

        $config['modules'][] = 'ApptSimpleAuthStubModuleAuthAllowed';

        $this->setApplicationConfig($config);
        $this->getApplication();

        Module::$config = array();
        Module::$allowed = null;
        TestController::$allowed = null;

        parent::setUp();
    }

    public function testNotAuthException()
    {
        Module::$allowed = function () {
            throw new \Exception('Test not auth exception message');
        };

        TestController::$allowed = function () {
            return false;
        };

        $this->dispatch('testModuleAuth');

        $this->assertResponseStatusCode(500);

        $response = $this->getResponse()->getContent();

        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Controller\TestController: false', $response);
        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Module: true', $response);
        $this->assertContains('Message: An error occurred during execution; please try again later.', $response);
        $this->assertContains('Exception: Exception', $response);
        $this->assertContains('Exception message: Test not auth exception message', $response);

        $this->assertApplicationException('Exception', 'Test not auth exception message');
    }

    public function testAuthAllowed()
    {
        Module::$allowed = function() {
            return false;
        };

        TestController::$allowed = function() {
            return false;
        };

        $this->dispatch('testModuleAuth');

        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();

        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Controller\TestController: false', $response);
        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Module: false', $response);
    }

    public function testModuleAuthNotAllowed()
    {
        Module::$allowed = function () {
            return true;
        };

        TestController::$allowed = function () {
            return false;
        };

        $this->dispatch('testModuleAuth');

        $this->assertResponseStatusCode(403);

        $response = $this->getResponse()->getContent();

        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Controller\TestController: false', $response);
        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Module: true', $response);
        $this->assertContains('Message: An error occurred during execution; please try again later.', $response);
        $this->assertContains('Exception: ApptSimpleAuth\Exception\AccessViolationException', $response);
        $this->assertContains('Exception message: Access not allowed', $response);
    }

    public function testControllerAuthNotAllowed()
    {
        Module::$allowed = function () {
            return false;
        };

        TestController::$allowed = function () {
            return true;
        };

        $this->dispatch('testModuleAuth');

        $this->assertResponseStatusCode(403);

        $response = $this->getResponse()->getContent();

        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Controller\TestController: true', $response);
        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Module: false', $response);
        $this->assertContains('Message: An error occurred during execution; please try again later.', $response);
        $this->assertContains('Exception: ApptSimpleAuth\Exception\AccessViolationException', $response);
        $this->assertContains('Exception message: Access not allowed', $response);
        $this->assertContains('Uri: testModuleAuth', $response);
    }

    public function testCustomMessage()
    {
        Module::$allowed = function () {
            return 'testCustomMessage';
        };

        TestController::$allowed = function () {
            return false;
        };

        $this->dispatch('testModuleAuth');

        $this->assertResponseStatusCode(403);

        $response = $this->getResponse()->getContent();

        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Controller\TestController: false', $response);
        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Module: testCustomMessage', $response);
        $this->assertContains('Message: An error occurred during execution; please try again later.', $response);
        $this->assertContains('Exception: ApptSimpleAuth\Exception\AccessViolationException', $response);
        $this->assertContains('Exception message: testCustomMessage', $response);
        $this->assertContains('Uri: testModuleAuth', $response);
    }

    public function testCustomException()
    {
        Module::$allowed = function () {
            $e = new AccessViolationException('testCustomException message');
            $e->setUri('testCustomException/uri');
            $e->setStatusCode(401);

            return $e;
        };

        TestController::$allowed = function () {
            return false;
        };

        $this->dispatch('testModuleAuth');

        $this->assertResponseStatusCode(401);

        $response = $this->getResponse()->getContent();

        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Controller\TestController: false', $response);
        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Module: testCustomException message', $response);
        $this->assertContains('Message: An error occurred during execution; please try again later.', $response);
        $this->assertContains('Exception: ApptSimpleAuth\Exception\AccessViolationException', $response);
        $this->assertContains('Exception message: testCustomException message', $response);
        $this->assertContains('Uri: testCustomException/uri', $response);
    }

    public function testCustomErrorTemplate()
    {
        Module::$config['errorTemplate'] = 'custom/auth/error';

        Module::$allowed = function () {
            return true;
        };

        TestController::$allowed = function () {
            return false;
        };

        $this->dispatch('testModuleAuth');

        $this->assertResponseStatusCode(403);

        $response = $this->getResponse()->getContent();

        $this->assertContains('CUSTOM AUTH ERROR', $response);
        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Controller\TestController: false', $response);
        $this->assertContains('\ApptSimpleAuthStubModuleAuthAllowed\Module: true', $response);
        $this->assertContains('Message: An error occurred during execution; please try again later.', $response);
        $this->assertContains('Exception: ApptSimpleAuth\Exception\AccessViolationException', $response);
        $this->assertContains('Exception message: Access not allowed', $response);
    }
}
