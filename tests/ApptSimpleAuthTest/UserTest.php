<?php
namespace ApptSimpleAuthTest;

use PHPUnit_Framework_TestCase;

use Zend\Crypt\Password\Bcrypt;
use ApptSimpleAuth\User;
use SimpleAcl\Role;

class UserTest extends PHPUnit_Framework_TestCase
{
    public function testUser()
    {
        $user = new User();

        $email = 'email';
        $pass = 'pass';

        $user->setEmail($email);
        $user->setPassword($pass);

        $this->assertEquals($email, $user->getEmail());

        $this->assertTrue($user->verifyPassword($pass));
    }

    public function testUserRemoveObjects()
    {
        $user = new User();

        $user->addRole(new Role('Role1'));
        $user->addRole(new Role('Role2'));
        $user->addRole(new Role('Role3'));

        $this->assertEquals(3, count($user->getRoles()));

        $user->removeRoles();

        $this->assertEquals(0, count($user->getRoles()));
    }

    public function testUserId()
    {
        $user = new User();
        $this->assertNotNull($user->getId());
    }
}