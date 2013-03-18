<?php
namespace ApptSimpleAuthTest;

use PHPUnit_Framework_TestCase;

use Zend\Crypt\Password\Bcrypt;
use ApptSimpleAuth\Acl;
use ApptSimpleAuth\Acl\Role;
use ApptSimpleAuth\Acl\Resource;
use SimpleAcl\Rule;

use Zend\Permissions\Acl\Role\GenericRole as ZendRole;
use Zend\Permissions\Acl\Resource\GenericResource as ZendResource;

class AclTest extends PHPUnit_Framework_TestCase
{
    public function testAclName()
    {
        $acl = new Acl('test_acl');

        $this->assertEquals('test_acl', $acl->getName());
    }

    public function testAclRemoveAllRules()
    {
        $acl = new Acl('test_acl');

        $role = new Role('test_role');
        $resource = new Resource('test_resource');

        $rule1 = new Rule('test_rule_1');
        $rule2 = new Rule('test_rule_2');
        $rule3 = new Rule('test_rule_3');

        $acl->addRule($role, $resource, $rule1, true);
        $acl->addRule($role, $resource, $rule2, true);
        $acl->addRule($role, $resource, $rule3, true);

        $this->assertSame($rule1, $acl->hasRule($rule1));
        $this->assertSame($rule2, $acl->hasRule($rule2));
        $this->assertSame($rule3, $acl->hasRule($rule3));

        $acl->removeAllRules();

        $this->assertFalse($acl->hasRule($rule1));
        $this->assertFalse($acl->hasRule($rule2));
        $this->assertFalse($acl->hasRule($rule3));
    }

    public function testZendCapability()
    {
        $acl = new Acl('test_acl');

        $role = new Role('test_role');
        $resource = new Resource('test_resource');

        $rule1 = new Rule('test_rule_1');

        $acl->addRule($role, $resource, $rule1, true);

        $this->assertFalse($acl->isAllowed());
        $this->assertFalse($acl->isAllowed(null, null, 'test_rule_1'));
        $this->assertFalse($acl->isAllowed('test_role', 'test_resource'));

        $this->assertTrue($acl->isAllowed(new ZendRole('test_role'), new ZendResource('test_resource'), 'test_rule_1'));
    }

    public function testZendCapabilityHasResource()
    {
        $acl = new Acl('test_acl');

        $role = new Role('test_role');
        $resource = new Resource('test_resource');

        $resourceParent = new Resource('test_resource_parent');
        $resourceChild = new Resource('test_resource_child');
        $resourceParent->addChild($resourceChild);

        $acl->addRule($role, $resource, 'test_rule_1', true);
        $acl->addRule($role, $resourceParent, 'test_rule_2', true);

        $this->assertFalse($acl->hasResource('unknown_resource'));
        $this->assertFalse($acl->hasResource(new ZendResource('unknown_resource')));

        $this->assertTrue($acl->hasResource('test_resource'));
        $this->assertTrue($acl->hasResource(new ZendResource('test_resource')));

        $this->assertTrue($acl->hasResource('test_resource_parent'));
        $this->assertTrue($acl->hasResource('test_resource_child'));
    }
}