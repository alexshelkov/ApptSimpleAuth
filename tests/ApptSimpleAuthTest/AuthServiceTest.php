<?php
namespace ApptSimpleAuthTest;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

use ApptSimpleAuth\AuthService;
use ApptSimpleAuth\ManagerService;

use Doctrine\ODM\MongoDB\DocumentManager;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

class AuthServiceTest extends AbstractConsoleControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(include TEST_APPLICATION_CONFIG);
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
     * @return ManagerService
     */
    public function getManager()
    {
        return $this->getApplicationServiceLocator()->get('appt.simple_auth.manager');
    }

    public function testEmptyIdentity()
    {
        $this->assertFalse($this->getAuth()->allowed());
    }

    public function testAuthenticate()
    {
        $email = uniqid(). '_test@test.ru';
        $pass = uniqid() . '_test_pass';

        $auth = $this->getAuth();

        $result = $auth->authenticate($email, $pass);

        $this->assertFalse($result->isValid());

        $this->getManager()->addUser($email, $pass);

        $this->getOdm()->clear();

        $result = $auth->authenticate($email, $pass);

        $this->assertTrue($result->isValid());

        return array('email' => $email, 'pass' => $pass);
    }

    /**
     * @depends testAuthenticate
     */
    public function testExistingIdentity($user)
    {
        $auth = $this->getAuth();

        $auth->authenticate($user['email'], $user['pass']);

        $this->getOdm()->clear();

        $this->assertEquals($user['email'], $auth->allowed()->getEmail());
    }

    public function testAccessRoleResourceEmptyIdentity()
    {
        $auth = $this->getAuth();

        $this->assertFalse($auth->allowed('Test', 'Test'));
    }

    /**
     * @depends testAuthenticate
     */
    public function testAccessRoleResource($user)
    {
        $auth = $this->getAuth();

        $auth->authenticate($user['email'], $user['pass']);

        $role = uniqid() . '_test_role';
        $resource = uniqid() . '_test_resource';
        $rule = uniqid() . '_test_rule';

        $this->getManager()->addUser($user['email'], null, $role);
        $this->getManager()->addRule($rule, $role, $resource, true);

        $this->getOdm()->clear();

        $this->assertEquals($user['email'], $auth->allowed($resource, $rule)->getEmail());
    }

    /**
     * @depends testAuthenticate
     */
    public function testAccessChangeAcl($user)
    {
        $auth = $this->getAuth();

        $auth->authenticate($user['email'], $user['pass']);

        $role = uniqid() . '_test_role';
        $resource = uniqid() . '_test_resource';
        $rule = uniqid() . '_test_rule';

        $acl = uniqid() . '_test_acl_1';
        $acl2 = uniqid() . '_test_acl_2';

        $this->getManager()->addUser($user['email'], null, $role);
        $this->getManager()->addRule($rule, $role, $resource, true, $acl);

        $this->getOdm()->clear();

        $this->assertFalse($auth->allowed($resource, $rule));
        $this->assertFalse($auth->setAcl($acl2)->allowed($resource, $rule));
        $this->assertEquals($user['email'], $auth->setAcl($acl)->allowed($resource, $rule)->getEmail());
    }

    public function testAccessRoleResourceRule()
    {
        $auth = $this->getAuth();

        $role = uniqid() . '_test_role';
        $resource = uniqid() . '_test_resource';
        $rule = uniqid() . '_test_rule';

        $this->getManager()->addRule($rule, $role, $resource, true);

        $this->getOdm()->clear();

        $this->assertTrue($auth->allowed($role, $resource, $rule));
    }

    public function testDeAuthenticate()
    {
        $email = uniqid() . '_test@test.ru';
        $pass = uniqid() . '_test_pass';

        $auth = $this->getAuth();

        $this->getManager()->addUser($email, $pass);

        $this->getOdm()->clear();

        $result = $auth->authenticate($email, $pass);

        $this->assertTrue($result->isValid());
        $this->assertEquals($email, $auth->allowed()->getEmail());

        $auth->deAuthenticate();

        $this->assertFalse($auth->allowed());

        return array('email' => $email, 'pass' => $pass);
    }

    public function testReturnResult()
    {
        $auth = $this->getAuth();

        $role = uniqid() . '_test_role';
        $resource = uniqid() . '_test_resource';
        $rule = uniqid() . '_test_rule';

        $this->getManager()->addRule($rule, $role, $resource, true);

        $this->getOdm()->clear();

        $this->assertTrue($auth->allowed($role, $resource, $rule));
        $this->assertInstanceOf('SimpleAcl\RuleResultCollection', $auth->getRuleResultCollection());
    }

    public function testBaseRule()
    {
        $rule = uniqid('test_rule_');
        $role = uniqid('test_role_');
        $resource = uniqid('test_resource_');

        $this->getManager()->addRule($rule, $role, $resource, true);

        $this->getOdm()->clear();

        $this->assertTrue($this->getAuth()->allowed($role, $resource, $rule));
    }

    public function testRuleWithParentRole()
    {
        $rule = uniqid('test_rule_');
        $role = uniqid('test_role_');
        $parentRole = uniqid('test_parent_role_');
        $resource = uniqid('test_resource_');

        $this->getManager()->addRole($role, $parentRole);

        $this->getManager()->addRule($rule, $role, $resource, true);

        $this->getOdm()->clear();

        $this->assertTrue($this->getAuth()->allowed($role, $resource, $rule));
        $this->assertTrue($this->getAuth()->allowed($parentRole, $resource, $rule));
    }

    public function testRuleWithChildResource()
    {
        $rule = uniqid('test_rule_');
        $role = uniqid('test_role_');
        $resource = uniqid('test_resource_');
        $childResource = uniqid('test_parent_resource_');

        $this->getManager()->addResource($resource, $childResource);

        $this->getManager()->addRule($rule, $role, $resource, true);

        $this->getOdm()->clear();

        $this->assertTrue($this->getAuth()->allowed($role, $resource, $rule));
        $this->assertTrue($this->getAuth()->allowed($role, $childResource, $rule));
    }

    public function testRuleWithUser()
    {
        $rule = uniqid('test_rule_');
        $role = uniqid('test_role_');
        $resource = uniqid('test_resource_');

        $email = uniqid('test_user_email_');
        $pass = uniqid('test_user_pass');

        $user = $this->getManager()->addUser($email, $pass, $role);

        $this->getManager()->addRule($rule, $role, $resource, true);

        $this->getOdm()->clear();

        $this->getAuth()->authenticate($email, $pass);

        $this->assertTrue($this->getAuth()->allowed($role, $resource, $rule));
        $this->assertEquals($user->getEmail(), $this->getAuth()->allowed($resource, $rule)->getEmail());
    }

    public function testRuleWithUserMultipleRoles()
    {
        $rule = uniqid('test_rule_');
        $role = uniqid('test_role_');
        $role2 = uniqid('test_role_2_');
        $resource = uniqid('test_resource_');
        $resource2 = uniqid('test_resource_2_');

        $email = uniqid('test_user_email_');
        $pass = uniqid('test_user_pass');

        $this->getManager()->addUser($email, $pass, $role);
        $user = $this->getManager()->addUser($email, null, $role2);

        $this->getManager()->addRule($rule, $role, $resource, true);
        $this->getManager()->addRule($rule, $role2, $resource2, true);

        $this->getOdm()->clear();

        $this->getAuth()->authenticate($email, $pass);

        $this->assertTrue($this->getAuth()->allowed($role, $resource, $rule));
        $this->assertFalse($this->getAuth()->allowed($role, $resource2, $rule));

        $this->assertFalse($this->getAuth()->allowed($role2, $resource, $rule));
        $this->assertTrue($this->getAuth()->allowed($role2, $resource2, $rule));

        $this->assertEquals($user->getEmail(), $this->getAuth()->allowed($resource, $rule)->getEmail());
        $this->assertEquals($user->getEmail(), $this->getAuth()->allowed($resource2, $rule)->getEmail());
    }

    public function testIsAuthRoute()
    {
        $auth = $this->getAuth();

        $event = new MvcEvent();
        $this->assertFalse($auth->isAuthRoute($event));

        $event = new MvcEvent();
        $rm = new RouteMatch(array());
        $event->setRouteMatch($rm);

        $this->assertFalse($auth->isAuthRoute($event));

        $event = new MvcEvent();
        $rm = new RouteMatch(array());
        $rm->setMatchedRouteName('aauth/login');
        $event->setRouteMatch($rm);
        $this->assertTrue($auth->isAuthRoute($event));

        $event = new MvcEvent();
        $rm = new RouteMatch(array());
        $rm->setMatchedRouteName('aauth/logout');
        $event->setRouteMatch($rm);
        $this->assertTrue($auth->isAuthRoute($event));
    }
}