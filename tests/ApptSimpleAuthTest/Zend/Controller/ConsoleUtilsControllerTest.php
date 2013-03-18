<?php
namespace ApptSimpleAuthTest\Zend\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

class ConsoleUtilsControllerTest extends AbstractConsoleControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(include TEST_APPLICATION_CONFIG);
        parent::setUp();
    }

    public function testClearDb()
    {
        $this->dispatch('aauth clear');
        $this->assertMatchedRouteName('appt.simple_auth.clear');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('clear');

        $this->assertConsoleOutputContains('Databases was cleared:');
    }

    public function testAclAdd()
    {
        $this->dispatch('aauth add acl test_acl');

        $this->assertMatchedRouteName('appt.simple_auth.acl_add');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('aclAdd');

        $this->assertConsoleOutputContains("Acl 'test_acl' was created");
    }

    public function testRoleAdd()
    {
        $this->dispatch('aauth add role test_role --child test_child_role');

        $this->assertMatchedRouteName('appt.simple_auth.role_or_resource_add');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('roleOrResourceAdd');

        $this->assertConsoleOutputContains("Role 'test_role' was created with child 'test_child_role'");
    }

    public function testUserAdd()
    {
        $this->dispatch('aauth add user test@test.ru test_pass --role test_role');

        $this->assertMatchedRouteName('appt.simple_auth.user_add');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('userAdd');

        $this->assertConsoleOutputContains("User 'test@test.ru' with password 'test_pass' was created");
    }

    public function testResourceAdd()
    {
        $this->dispatch('aauth add resource test_resource --child test_child_resource');

        $this->assertMatchedRouteName('appt.simple_auth.role_or_resource_add');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('roleOrResourceAdd');

        $this->assertConsoleOutputContains("Resource 'test_resource' was created with child 'test_child_resource'");
    }

    public function testRuleAllowAdd()
    {
        $this->dispatch('aauth add rule test_role_1 test_resource_1 test_rule_1 true');

        $this->assertMatchedRouteName('appt.simple_auth.rule_add');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('ruleAdd');

        $this->assertConsoleOutputContains("Allow rule 'test_rule_1' was created for role 'test_role_1' and resource 'test_resource_1' in 'Test'");
    }

    public function testRuleDenyAdd()
    {
        $this->dispatch('aauth add rule test_role_2 test_resource_2 test_rule_2 false');

        $this->assertMatchedRouteName('appt.simple_auth.rule_add');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('ruleAdd');

        $this->assertConsoleOutputContains("Deny rule 'test_rule_2' was created for role 'test_role_2' and resource 'test_resource_2' in 'Test'");
    }

    public function testNotFoundRemove()
    {
        $acl = 'Test_Acl_' . uniqid();
        $this->dispatch("aauth remove acl $acl");
        $this->assertMatchedRouteName('appt.simple_auth.remove');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('remove');

        $this->assertConsoleOutputContains("Acl '$acl' was not found");
    }

    /**
     * @depends testAclAdd
     */
    public function testListAcl()
    {
        $this->dispatch("aauth list acl");
        $this->assertMatchedRouteName('appt.simple_auth.list');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('list');

        $this->assertConsoleOutputContains("test_acl");
    }

    /**
     * @depends testRuleAllowAdd
     * @depends testRuleDenyAdd
     */
    public function testListRule()
    {
        $this->dispatch("aauth list rule");
        $this->assertMatchedRouteName('appt.simple_auth.list');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('list');

        $this->assertConsoleOutputContains("test_rule_1 test_role_1 test_resource_1 true");
        $this->assertConsoleOutputContains("test_rule_2 test_role_2 test_resource_2 false");
    }

    /**
     * @depends testUserAdd
     */
    public function testListUser()
    {
        $this->dispatch("aauth list user");
        $this->assertMatchedRouteName('appt.simple_auth.list');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('list');

        $this->assertConsoleOutputContains('test@test.ru (test_role)');
    }

    /**
     * @depends testRoleAdd
     */
    public function testListRole()
    {
        $this->dispatch("aauth list role");
        $this->assertMatchedRouteName('appt.simple_auth.list');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('list');

        $this->assertConsoleOutputContains('test_role -> test_child_role');
    }

    public function testAllowedNoRules()
    {
        $this->dispatch("aauth allowed -d test_role test_resource test_unknown_rule");
        $this->assertMatchedRouteName('appt.simple_auth.allowed');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('allowed');

        $this->assertConsoleOutputContains("no");
        $this->assertConsoleOutputContains("Rules applied (first wins):");
        $this->assertConsoleOutputContains("none");
    }

    /**
     * @depends testRuleAllowAdd
     */
    public function testAllowed()
    {
        $this->dispatch("aauth allowed -d test_role_1 test_resource_1 test_rule_1");
        $this->assertMatchedRouteName('appt.simple_auth.allowed');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('allowed');

        $this->assertConsoleOutputContains("yes");
        $this->assertConsoleOutputContains("Rules applied (first wins):");
        $this->assertConsoleOutputContains("allow 0");
    }

    /**
     * @depends testResourceAdd
     */
    public function testListResource()
    {
        $this->dispatch("aauth list resource");
        $this->assertMatchedRouteName('appt.simple_auth.list');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('list');

        $this->assertConsoleOutputContains('test_resource -> test_child_resource');
    }

    /**
     * @depends testAclAdd
     */
    public function testRemove()
    {
        $this->dispatch("aauth remove acl test_acl");
        $this->assertMatchedRouteName('appt.simple_auth.remove');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('remove');

        $this->assertConsoleOutputContains("Acl 'test_acl' was removed");
    }

    public function testUserRoleRemoveNotFound()
    {
        $badUser = 'User_' . uniqid();
        $this->dispatch("aauth remove user role $badUser role");
        $this->assertMatchedRouteName('appt.simple_auth.user_role_remove');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('userRoleRemove');

        $this->assertConsoleOutputContains("Role or user not found");
    }

    /**
     * @depends testUserAdd
     */
    public function testUserRoleRemove()
    {
        $this->dispatch("aauth remove user role test@test.ru test_role");
        $this->assertMatchedRouteName('appt.simple_auth.user_role_remove');
        $this->assertControllerClass('ConsoleUtilsController');
        $this->assertActionName('userRoleRemove');

        $this->assertConsoleOutputContains("Role 'test_role' was removed from 'test@test.ru'");
    }
}
