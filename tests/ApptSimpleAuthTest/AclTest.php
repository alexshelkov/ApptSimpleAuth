<?php
namespace ApptSimpleAuthTest;

use DoctrineMongoODMModuleTest\AbstractTest as DoctrineModuleTest;

use ApptSimpleAuth\Acl;

use SimpleAcl\Role;
use SimpleAcl\Resource;
use ApptSimpleAuth\Rule;

use ApptSimpleAuth\User;

class AclTest extends DoctrineModuleTest
{
    /**
     * @return Acl
     */
    public function getAclService()
    {
        return $this->serviceManager->get('appt.simple_auth.acl');
    }

    public function testEmptyAcl()
    {
        $acl = $this->getAclService();

        $this->assertFalse($acl->isAllowed('Role', 'Resource', 'Rule'));

        $acl->addRule(new Role('Role'), new Resource('Resource'), new Rule('Rule'), true);

        $this->assertTrue($acl->isAllowed('Role', 'Resource', 'Rule'));

        $acl->removeAllRules();

        $this->assertFalse($acl->isAllowed('Role', 'Resource', 'Rule'));
    }

    public function testSaveAcl()
    {
        /** @var \Doctrine\ODM\MongoDB\DocumentManager $odm */
        $odm = $this->getDocumentManager();

        $acl = $this->getAclService();

        $odm->persist($acl);

        $odm->flush();

        $odm->detach($acl);

        /** @var Acl $aclFind */
        $aclFind = $odm->find(get_class($acl), $acl->getName());

        $this->assertSame($aclFind->getName(), $acl->getName());
    }

    public function testSaveAclWithRules()
    {
        /** @var \Doctrine\ODM\MongoDB\DocumentManager $odm */
        $odm = $this->getDocumentManager();

        $acl = $this->getAclService();

        $acl->addRule(new Role('Role'), new Resource('Resource'), new Rule('Rule'), true);

        $odm->persist($acl);

        $odm->flush();

        $odm->detach($acl);

        /** @var Acl $acl */
        $acl = $odm->find(get_class($acl), $acl->getName());

        $this->assertTrue($acl->isAllowed('Role', 'Resource', 'Rule'));
    }

    public function testSaveUserAsRoleAggregate()
    {
        /** @var \Doctrine\ODM\MongoDB\DocumentManager $odm */
        $odm = $this->getDocumentManager();

        $user = new User();

        $user->setEmail('test@test.ru');

        $viewer = new Role('Viewer');
        $editor = new Role('Editor');
        $submitter = new Role('Submitter');

        $roles = array('Viewer' => $viewer, 'Editor' => $editor, 'Submitter' => $submitter);

        $user->setRoles($roles);

        $odm->persist($user);

        $odm->flush();

        $odm->detach($user);

        /** @var User $user */
        $user = $odm->find(get_class($user), $user->getEmail());

        $findRoles = array();
        foreach ( $user->getRoles() as $role) {
            $findRoles[] = $role->getName();
        }

        $this->assertEquals(array_keys($roles), $findRoles);
    }

    public function testSaveAclWithSavedUser()
    {
        /** @var \Doctrine\ODM\MongoDB\DocumentManager $odm */
        $odm = $this->getDocumentManager();

        $user = new User();

        $user->setEmail('test@test.ru');

        $viewer = new Role('Viewer');
        $editor = new Role('Editor');
        $submitter = new Role('Submitter');

        $user->setRoles(array($viewer, $editor, $submitter));

        $odm->persist($user);

        $acl = $this->getAclService();

        $page = new Resource('Page');
        $acl->addRule($viewer, $page, new Rule('View'), true);
        $acl->addRule($editor, $page, new Rule('Edit'), true);
        $acl->addRule($submitter, $page, new Rule('Submit'), true);

        $odm->persist($acl);

        $odm->flush();

        $odm->detach($acl);
        $odm->detach($user);

        /** @var Acl $acl */
        $acl = $odm->find(get_class($acl), $acl->getName());

        $user = $odm->find(get_class($user), $user->getEmail());

        $this->assertTrue($acl->isAllowed($user, 'Page', 'View'));
        $this->assertTrue($acl->isAllowed($user, 'Page', 'Edit'));
        $this->assertTrue($acl->isAllowed($user, 'Page', 'Submit'));
    }
}

