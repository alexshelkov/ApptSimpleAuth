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
        $this->dispatch('/aauth/logout/accept');
        $this->assertResponseStatusCode(404);
    }

    public function testLogoutNoCsrf()
    {
        $this->dispatch('/aauth/logout/accept', 'post');
        $this->assertResponseStatusCode(403);
    }

    public function testLogoutNoParams()
    {
        $uri = $this->getLogout()->get('success_uri')->getValue();

        $this->assertEmpty($uri);

        $csrf = $this->getLogout()->get('csrf')->getValue();

        $this->dispatch('/aauth/logout/accept', 'POST', array('csrf' => $csrf, 'success_uri' => $uri));

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

        $uri = $this->getLogout()->get('success_uri')->getValue();

        $this->assertEmpty($uri);

        $csrf = $this->getLogout()->get('csrf')->getValue();

        $this->dispatch('/aauth/logout/accept', 'POST', array('csrf' => $csrf, 'success_uri' => $uri));

        $this->assertContains("Please setup appt['simple_auth']['forms']['logout']['success_redirect_params']", $this->getResponse()->getContent());
    }

    public function testLogoutToUri()
    {
        $expectedUri = 'http://www.example.com';

        Module::$config = array(
            'forms' => array(
                'logout' => array(
                    'success_redirect_params' => $expectedUri
                )
            ),
        );

        $uri = $this->getLogout()->get('success_uri')->getValue();

        $this->assertEquals($expectedUri, $uri);

        $csrf = $this->getLogout()->get('csrf')->getValue();

        $this->dispatch('/aauth/logout/accept', 'POST', array('csrf' => $csrf, 'success_uri' => $uri));

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', $expectedUri);
    }

    public function testLogoutToRoute()
    {
        $expectedUri = 'testLogoutRedirectToRoute';

        Module::$config = array(
            'forms' => array(
                'logout' => array(
                    'success_redirect_params' => array(
                        'route' => 'test_redirect'
                    ),
                )
            ),
        );

        $uri = $this->getLogout()->get('success_uri')->getValue();

        $this->assertEquals($expectedUri, $uri);

        $csrf = $this->getLogout()->get('csrf')->getValue();

        $this->dispatch('/aauth/logout/accept', 'POST', array('csrf' => $csrf, 'success_uri' => $uri));

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', $expectedUri    );
    }

    public function testLogoutSetSuccessRedirectUri()
    {
        $expectedUri = 'testLogoutSetSuccessRedirectUri';

        $this->getLogout()->setSuccessRedirectUri($expectedUri);
        $uri = $this->getLogout()->get('success_uri')->getValue();

        $this->assertEquals($expectedUri, $uri);

        $csrf = $this->getLogout()->get('csrf')->getValue();

        $this->dispatch('/aauth/logout/accept', 'POST', array('csrf' => $csrf, 'success_uri' => $uri));

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', $expectedUri);
    }

    public function testLoginGetRequestDisableControllerDisplay()
    {
        $this->dispatch('/aauth/login/accept');
        $this->assertResponseStatusCode(404);

        $content = $this->getResponse()->getContent();

        $expectedContent = 'Not found';

        $this->assertContains($expectedContent, $content);
    }

    public function testLoginGetRequest()
    {
        Module::$config = array(
            'forms' => array(
                'login' => array(
                    'controller_display_enable' => true
                )
            ),
        );

        $csrf = $this->getLogin()->get('csrf')->getValue();

        $this->dispatch('/aauth/login/accept');

        $content = $this->getResponse()->getContent();

        $expectedContent = '<form action="/aauth/login/accept" method="post" name="login" id="login"><input name="user[email]" type="text" value=""><input name="user[password]" type="text" value=""><input name="submit" type="submit" value="Login"><input name="user[auth_error]" type="hidden" value="0"><input type="hidden" name="success_uri" value=""><input type="hidden" name="fail_uri" value="http:/"><input type="hidden" name="csrf" value="' . $csrf . '"></form>';

        $this->assertContains($expectedContent, $content);
    }

    public function testLoginGetRequestWithAuthErrorAndRenderer()
    {
        Module::$config = array(
            'forms' => array(
                'renderer' => 'ViewRenderer',
                'login' => array(
                    'controller_display_enable' => true
                )
            ),
        );

        $csrf = $this->getLogin()->get('csrf')->getValue();

        $this->dispatch('/aauth/login/accept?auth-error=fail&email=test');

        $content = $this->getResponse()->getContent();

        $expectedContent = '<form action="/aauth/login/accept" method="post" name="login" id="login"><input name="user[email]" type="text" value="test"><input name="user[password]" type="text" value=""><input name="submit" type="submit" value="Login"><input name="user[auth_error]" type="hidden" value="fail"><input type="hidden" name="success_uri" value=""><input type="hidden" name="fail_uri" value="http:/"><input type="hidden" name="csrf" value="' . $csrf . '"><ul><li>Incorrect email or password provided</li></ul></form>';

        $this->assertContains($expectedContent, $content);
    }

    public function testLoginFail()
    {
        $this->dispatch('/aauth/login/accept', 'POST', array('fail_uri' => ''));

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', '?auth-error=fail');
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
            'fail_uri' => ''
        );

        $this->dispatch('/aauth/login/accept', 'POST', $params);

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', '?auth-error=fail&email=test%40test.ru');

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
            'csrf' => $csrf,
            'fail_uri' => ''
        );

        $this->dispatch('/aauth/login/accept', 'POST', $params);

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', '?auth-error=fail&email=test');

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
            'csrf' => $csrf,
            'fail_uri' => ''
        );

        $this->dispatch('/aauth/login/accept', 'POST', $params);

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', '?auth-error=fail&email=test_not_have%40test.ru');

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
            'csrf' => $csrf,
            'success_uri' => ''
        );

        $this->dispatch('/aauth/login/accept', 'POST', $params);

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
        $uri = $this->getLogin()->get('success_uri')->getValue();

        $email = 'test2@test.ru';
        $pass = 'test2';
        $this->getManager()->addUser($email, $pass);

        $params = array(
            'user' => array(
                'email' => strtoupper($email),
                'password' => $pass
            ),
            'csrf' => $csrf,
            'success_uri' => $uri
        );

        $this->dispatch('/aauth/login/accept', 'POST', $params);

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
        $uri = $this->getLogin()->get('success_uri')->getValue();

        $email = 'test2@test.ru';
        $pass = 'test2';
        $this->getManager()->addUser($email, $pass);

        $params = array(
            'user' => array(
                'email' => strtoupper($email),
                'password' => $pass
            ),
            'csrf' => $csrf,
            'success_uri' => $uri
        );

        $this->dispatch('/aauth/login/accept', 'POST', $params);

        $this->assertResponseStatusCode(301);
        $this->assertResponseHeaderContains('Location', 'testLogoutRedirectToRoute');

        $this->getOdm()->clear();

        $this->assertEquals($email, $this->getAuth()->allowed()->getEmail());
    }
}
