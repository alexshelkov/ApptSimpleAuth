<?php
namespace ApptSimpleAuthTest\Zend\View\Helper;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use ApptSimpleAuthStubModuleForms\Module;

use ApptSimpleAuth\Zend\Form\Logout;
use ApptSimpleAuth\Zend\Form\Login;

use ApptSimpleAuth\ManagerService;
use ApptSimpleAuth\AuthService;
use Doctrine\ODM\MongoDB\DocumentManager;

class DisplayFormsTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $config = include TEST_APPLICATION_CONFIG;
        $config['module_listener_options']['module_paths']['ApptSimpleAuthStubModuleForms'] =
                'tests/ApptSimpleAuthStub/ApptSimpleAuthStubModuleForms';

        $config['modules'][] = 'ApptSimpleAuthStubModuleForms';

        $this->setApplicationConfig($config);
        $this->getApplication();

        Module::$config = array();

        parent::setUp();
    }

    /**
     * @return AuthService
     */
    public function getAuth()
    {
        return $this->getApplicationServiceLocator()->get('appt.simple_auth.auth');
    }

    /**
     * @return DocumentManager
     */
    public function getOdm()
    {
        return $this->getApplicationServiceLocator()->get('doctrine.documentmanager.odm_default');
    }

    /**
     * @return Login
     */
    protected function getLogin()
    {
        return $this->getApplicationServiceLocator()->get('FormElementManager')->get('appt.simple_auth.form.login');
    }

    /**
     * @return ManagerService
     */
    protected function getManager()
    {
        return $this->getApplicationServiceLocator()->get('appt.simple_auth.manager');
    }

    /**
     * @return Logout
     */
    protected function getLogout()
    {
        return $this->getApplicationServiceLocator()->get('FormElementManager')->get('appt.simple_auth.form.logout');
    }

    public function testDisplayLogin()
    {
        $csrf = $this->getLogin()->get('csrf')->getValue();

        $this->dispatch('login');

        $content = $this->getResponse()->getContent();

        $expectedContent = '<form action="/aauth/login/accept" method="post" name="login" id="login"><input name="user[email]" type="text" value=""><input name="user[password]" type="text" value=""><input name="submit" type="submit" value="Login"><input name="user[auth_error]" type="hidden" value="0"><input type="hidden" name="success_uri" value=""><input type="hidden" name="fail_uri" value="http:/"><input type="hidden" name="csrf" value="' . $csrf . '"></form>';

        $this->assertContains($expectedContent, $content);
    }

    public function testDisplayLogout()
    {
        $csrf = $this->getLogout()->get('csrf')->getValue();

        $this->dispatch('logout');

        $content = $this->getResponse()->getContent();

        $expectedContent = '<form action="/aauth/logout/accept" method="post" name="logout" id="logout"><input name="submit" type="submit" value="Logout"><input type="hidden" name="success_uri" value=""><input type="hidden" name="csrf" value="' . $csrf . '"></form>';

        $this->assertContains($expectedContent, $content);
    }
}
