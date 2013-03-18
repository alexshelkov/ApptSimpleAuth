<?php
namespace ApptSimpleAuthTest;

use ApptSimpleAuth\Acl;
use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

use ApptSimpleAuth\ManagerService;
use Doctrine\ODM\MongoDB\DocumentManager;

class ManagerServiceTest extends AbstractConsoleControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(include TEST_APPLICATION_CONFIG);
        parent::setUp();
    }

    /**
     * @return DocumentManager
     */
    protected function getOdm()
    {
        return $this->getApplicationServiceLocator()->get('doctrine.documentmanager.odm_default');
    }

    /**
     * @return ManagerService
     */
    protected function getManager()
    {
        return $this->getApplicationServiceLocator()->get('appt.simple_auth.manager');
    }

    public function testAddAcl()
    {
        $name = uniqid('test_acl');

        $acl = $this->getManager()->addAcl($name);

        $this->getOdm()->clear();

        $found = $this->getOdm()->find(get_class($acl), $name);

        $this->assertEquals($name, $found->getName());

        return $name;
    }

    public function testAddUser()
    {
        $email = uniqid('test_user_');
        $pass = uniqid('test_pass_');

        $user = $this->getManager()->addUser($email, $pass);

        $this->getOdm()->clear();

        $found = $this->getOdm()->getRepository(get_class($user))->findOneBy(array('email' => $email));

        $this->assertEquals($email, $found->getEmail());

        return $email;
    }

    public function testAddRole()
    {
        $name = uniqid('test_role_');

        $role = $this->getManager()->addRole($name);

        $this->getOdm()->clear();

        $found = $this->getOdm()->find(get_class($role), $name);

        $this->assertEquals($name, $found->getName());

        return $name;
    }

    public function testAddRoleParent()
    {
        $name = uniqid('test_role_');
        $child = uniqid('test_role_child_');

        $role = $this->getManager()->addRole($name, $child);

        $this->getOdm()->clear();

        $found = $this->getOdm()->find(get_class($role), $name);

        $this->assertEquals($name, $found->getName());
        $c = $found->getChildren();
        $this->assertEquals($child, $c[0]->getName());
    }

    public function testAddResource()
    {
        $name = uniqid('test_resource_');

        $resource = $this->getManager()->addResource($name);

        $this->getOdm()->clear();

        $found = $this->getOdm()->find(get_class($resource), $name);

        $this->assertEquals($name, $found->getName());
    }

    public function testAddResourceParent()
    {
        $name = uniqid('test_resource_');
        $child = uniqid('test_resource_child_');

        $resource = $this->getManager()->addResource($name, $child);

        $this->getOdm()->clear();

        $found = $this->getOdm()->find(get_class($resource), $name);

        $this->assertEquals($name, $found->getName());
        $c = $found->getChildren();
        $this->assertEquals($child, $c[0]->getName());

        return array('name' => $name, 'child' => $child);
    }

    public function testAddUserRole()
    {
        $email = uniqid('test_user_');
        $pass = uniqid('test_pass_');

        $role = uniqid('test_role_');

        $user = $this->getManager()->addUser($email, $pass, $role);

        $this->getOdm()->clear();

        $found = $this->getOdm()->getRepository(get_class($user))->findOneBy(array('email' => $email));

        $this->assertEquals($email, $found->getEmail());
        $this->assertContains($role, $found->getRolesNames());

        return array('email' => $email, 'role' => $role);
    }

    public function testAddRule()
    {
        $ruleName = uniqid('test_rule_');
        $role = uniqid('test_role_');
        $resource = uniqid('test_resource_');

        $this->getManager()->addRule($ruleName, $role, $resource, true);

        $this->getOdm()->clear();

        $found = $this->getOdm()->getRepository(ManagerService::RULE_CLASS)->findOneBy(array('name' => $ruleName));

        $this->assertEquals($ruleName, $found->getName());
        $this->assertTrue($found->getAction());
        $this->assertEquals($role, $found->getRole()->getName());
        $this->assertEquals($resource, $found->getResource()->getName());

        return $ruleName;
    }

    public function testUserRoleRemoveBadUser()
    {
        $this->assertFalse($this->getManager()->userRoleRemove(uniqid(), uniqid()));
    }

    /**
     * @depends testAddUserRole
     */
    public function testUserRoleRemoveBadRole($user)
    {
        $this->assertFalse($this->getManager()->userRoleRemove($user['email'], uniqid()));
    }

    /**
     * @depends testAddUserRole
     */
    public function testUserRoleRemove($user)
    {
        $this->assertTrue($this->getManager()->userRoleRemove($user['email'], $user['role']));
    }

    public function testRemoveBadObject()
    {
        $this->setExpectedException('ApptSimpleAuth\Exception\RuntimeException', 'Invalid shortcut given');
        $this->assertTrue($this->getManager()->remove('bad_object', uniqid()));
    }

    public function testNotRemove()
    {
        $this->assertFalse($this->getManager()->remove('acl', uniqid()));
    }

    /**
     * @depends testAddUser
     */
    public function testRemoveUser($email)
    {
        $this->assertTrue($this->getManager()->remove('user', $email));
    }

    /**
     * @depends testAddAcl
     */
    public function testRemoveAcl($aclName)
    {
        $this->assertTrue($this->getManager()->remove('acl', $aclName));
    }

    /**
     * @depends testAddRule
     */
    public function testRemoveRule($ruleName)
    {
        $rule = $this->getOdm()->getRepository(ManagerService::RULE_CLASS)->findOneBy(array('name' => $ruleName));

        $this->assertInstanceOf('SimpleAcl\Rule', $rule);

        $acl = $this->getOdm()->getRepository(ManagerService::ACL_CLASS)->findOneBy(array('rules' => $rule->getId()));

        $this->assertInstanceOf('ApptSimpleAuth\Acl', $acl);

        $this->assertTrue($this->getManager()->remove('rule', $ruleName));

        $acl = $this->getOdm()->getRepository(ManagerService::ACL_CLASS)->findOneBy(array('rules' => $rule->getId()));
        $this->assertNull($acl);
    }

    public function testListBadObject()
    {
        $this->setExpectedException('ApptSimpleAuth\Exception\RuntimeException', 'Invalid shortcut given');
        $this->assertTrue($this->getManager()->listObjects('bad_object', 0, 1));
    }

    /**
     * @depends testAddRole
     */
    public function testRemoveRole($name)
    {
        $this->assertTrue($this->getManager()->remove('role', $name));
    }

    /**
     * @depends testAddResourceParent
     */
    public function testRemoveResourceChild($resource)
    {
        $this->assertTrue($this->getManager()->remove('resource', $resource['child']));

        $this->getOdm()->clear();

        $found = $this->getOdm()->getRepository(ManagerService::RESOURCE_CLASS)->findOneBy(array('name' => $resource['name']));

        $this->assertEquals(0, count($found->getChildren()));
    }

    public function testRemoveRuleById()
    {
        $manager = $this->getManager();
        $odm = $this->getOdm();

        $ruleName = 'test_rule_' . uniqid();

        $manager->addRule($ruleName, uniqid(), uniqid());
        $odm->clear();

        $rule1 = $this->getOdm()->getRepository(ManagerService::RULE_CLASS)->findOneBy(array('name' => $ruleName));
        $this->assertEquals($ruleName, $rule1->getName());

        $manager->addRule($ruleName, uniqid(), uniqid());
        $odm->clear();

        $this->assertFalse($manager->remove('rule', $rule1->getId()));
        $this->assertTrue($manager->remove('rule', $rule1->getId(), false));

        $this->assertNotNull($this->getOdm()->getRepository(ManagerService::RULE_CLASS)->findOneBy(array('name' => $ruleName)));
    }

    /**
     * @depends testAddUser
     */
    public function testList()
    {
        $this->assertEquals(1, $this->getManager()->listObjects('user', 0, 1)->count(true));
    }

    public function testClearDb()
    {
        $this->assertNotEmpty($this->getManager()->clearDb());
    }
}

