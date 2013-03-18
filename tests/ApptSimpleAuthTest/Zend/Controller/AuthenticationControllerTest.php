<?php
namespace ApptSimpleAuthTest\Zend\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use ApptSimpleAuthStubModuleForms\Module;

use ApptSimpleAuth\Zend\Form\Logout;
use ApptSimpleAuth\Zend\Form\Login;

use ApptSimpleAuth\ManagerService;
use ApptSimpleAuth\AuthService;
use Doctrine\ODM\MongoDB\DocumentManager;

class AuthenticationControllerTest extends AbstractHttpControllerTestCase
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

    public function testSameInstances()
    {
        $this->assertSame($this->getLogin()->setData(array()), $this->getLogin());
        $this->assertSame($this->getLogin()->isValid(), $this->getLogin()->isValid());

        $this->assertSame($this->getLogout()->setData(array()), $this->getLogout());
        $this->assertSame($this->getLogout()->isValid(), $this->getLogout()->isValid());

        $this->assertSame(
            $this->getApplicationServiceLocator('appt.simple_auth.form.fieldset.user'),
            $this->getApplicationServiceLocator('appt.simple_auth.form.fieldset.user')
        );
    }

    public function testLogoutNotPost()
    {
        $this->dispatch('aauth/logout/accept');
        $this->assertResponseStatusCode(404);
    }

    public function testLogoutNoCsrf()
    {
        $this->dispatch('aauth/logout/accept', 'post');
        $this->assertResponseStatusCode(403);
    }

    public function testLogoutNoParams()
    {
        $csrf = $this->getLogout()->get('csrf')->getValue();

        $this->dispatch('aauth/logout/accept', 'POST', array('csrf' => $csrf));

        $this->assertContains("Please setup appt['simple_auth']['forms']['logout']['success_redirect_params']", $this->getResponse()->getContent());
    }

    public function testLogoutBadParams()
    {
        Module::$config = array(
            'forms' => array(
                'logout' => array(
                    'success_redirect_params' => array()
                )
            ),
        );

        $csrf = $this->getLogout()->get('csrf')->getValue();

        $this->dispatch('aauth/logout/accept', 'POST', array('csrf' => $csrf));

        $this->assertContains("Please setup appt['simple_auth']['forms']['logout']['success_redirect_params']", $this->getResponse()->getContent());
    }

    public function testLogoutToUri()
    {
        Module::$config = array(
            'forms' => array(
                'logout' => array(
                    'success_redirect_params' => 'http://www.example.com'
                )
            ),
        );

        $csrf = $this->getLogout()->get('csrf')->getValue();

        $this->dispatch('aauth/logout/accept', 'POST', array('csrf' => $csrf));

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', 'http://www.example.com');
    }

    public function testLogoutToRoute()
    {
        Module::$config = array(
            'forms' => array(
                'logout' => array(
                    'success_redirect_params' => array(
                        'route' => 'test_redirect'
                    ),
                )
            ),
        );

        $csrf = $this->getLogout()->get('csrf')->getValue();

        $this->dispatch('aauth/logout/accept', 'POST', array('csrf' => $csrf));

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', 'testLogoutRedirectToRoute');
    }

    public function testLoginGetRequest()
    {
        $csrf = $this->getLogin()->get('csrf')->getValue();

        $this->dispatch('aauth/login/accept');

        $content = $this->getResponse()->getContent();

        $expectedContent = '<form action="aauth/login/accept" method="post" name="login" id="login"><input name="user[email]" type="text" value=""><input name="user[password]" type="text" value=""><input name="submit" type="submit" value="Login"><input type="hidden" name="csrf" value="' . $csrf . '"></form>';

        $this->assertContains($expectedContent, $content);
    }

    public function testLoginGetRequestWithAuthErrorAndRenderer()
    {
        Module::$config = array(
            'forms' => array(
                'renderer' => 'ViewRenderer'
            ),
        );

        $csrf = $this->getLogin()->get('csrf')->getValue();

        $this->dispatch('aauth/login/accept?auth-error=fail&email=test');

        $content = $this->getResponse()->getContent();

        $expectedContent = '<form action="aauth/login/accept" method="post" name="login" id="login"><input name="user[email]" type="text" value="test"><input name="user[password]" type="text" value=""><input name="submit" type="submit" value="Login"><input type="hidden" name="csrf" value="' . $csrf . '"><ul><li>Incorrect email or password provided</li></ul></form>';

        $this->assertContains($expectedContent, $content);
    }

    public function testLoginFail()
    {
        $this->dispatch('aauth/login/accept', 'POST');

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', 'aauth/login/accept?auth-error=fail');
    }

    public function testLoginBadCsrf()
    {
        $email = 'test@test.ru';
        $pass = 'test';
        $this->getManager()->addUser($email, $pass);

        $params = array(
            'user' => array(
                'email' => strtoupper($email),
                'password' => $pass
            ),
        );

        $this->dispatch('aauth/login/accept', 'POST', $params);

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', 'aauth/login/accept?auth-error=fail&email=test%40test.ru');

        $this->getOdm()->clear();

        $this->assertFalse((bool)$this->getAuth()->allowed());
    }

    public function testLoginBadEmail()
    {
        $csrf = $this->getLogin()->get('csrf')->getValue();

        $email = 'test'; // not valid email
        $pass = 'test';
        $this->getManager()->addUser($email, $pass);

        $params = array(
            'user' => array(
                'email' => strtoupper($email),
                'password' => $pass
            ),
            'csrf' => $csrf
        );

        $this->dispatch('aauth/login/accept', 'POST', $params);

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', 'aauth/login/accept?auth-error=fail&email=test');

        $this->getOdm()->clear();

        $this->assertFalse((bool)$this->getAuth()->allowed());
    }

    public function testLoginBadUser()
    {
        $csrf = $this->getLogin()->get('csrf')->getValue();

        $email = 'test_not_have@test.ru';
        $pass = 'test';

        $params = array(
            'user' => array(
                'email' => $email,
                'password' => $pass
            ),
            'csrf' => $csrf
        );

        $this->dispatch('aauth/login/accept', 'POST', $params);

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', 'aauth/login/accept?auth-error=fail&email=test_not_have%40test.ru');

        $this->getOdm()->clear();

        $this->assertFalse((bool)$this->getAuth()->allowed());
    }

    public function testLoginSuccessNoRedirect()
    {
        $csrf = $this->getLogin()->get('csrf')->getValue();

        $email = 'test@test.ru';
        $pass = 'test';
        $this->getManager()->addUser($email, $pass);

        $params = array(
            'user' => array(
                'email' => strtoupper($email),
                'password' => $pass
            ),
            'csrf' => $csrf
        );

        $this->dispatch('aauth/login/accept', 'POST', $params);

        $this->assertResponseStatusCode(200);

        $content = $this->getResponse()->getContent();

        $this->assertContains("Please setup appt['simple_auth']['forms']['login']['success_redirect_params']", $content);

        $this->getOdm()->clear();

        $this->assertEquals($email, $this->getAuth()->allowed()->getEmail());
    }

    public function testLoginToUri()
    {
        Module::$config = array(
            'forms' => array(
                'login' => array(
                    'success_redirect_params' => 'http://example.com'
                )
            ),
        );

        $csrf = $this->getLogin()->get('csrf')->getValue();

        $email = 'test2@test.ru';
        $pass = 'test2';
        $this->getManager()->addUser($email, $pass);

        $params = array(
            'user' => array(
                'email' => strtoupper($email),
                'password' => $pass
            ),
            'csrf' => $csrf
        );

        $this->dispatch('aauth/login/accept', 'POST', $params);

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', 'http://example.com');

        $this->getOdm()->clear();

        $this->assertEquals($email, $this->getAuth()->allowed()->getEmail());
    }

    public function testLoginToRoute()
    {
        Module::$config = array(
            'forms' => array(
                'login' => array(
                    'success_redirect_params' => array(
                        'route' => 'test_redirect'
                    )
                )
            ),
        );

        $csrf = $this->getLogin()->get('csrf')->getValue();

        $email = 'test2@test.ru';
        $pass = 'test2';
        $this->getManager()->addUser($email, $pass);

        $params = array(
            'user' => array(
                'email' => strtoupper($email),
                'password' => $pass
            ),
            'csrf' => $csrf
        );

        $this->dispatch('aauth/login/accept', 'POST', $params);

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', 'testLogoutRedirectToRoute');

        $this->getOdm()->clear();

        $this->assertEquals($email, $this->getAuth()->allowed()->getEmail());
    }
}
