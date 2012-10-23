<?php
namespace ApptSimpleAuthTest;

use DoctrineMongoODMModuleTest\AbstractTest as DoctrineModuleTest;

use ApptSimpleAuth\User;

class AuthTest extends DoctrineModuleTest
{
    public function getAuthenticationService()
    {
        return $this->serviceManager->get('appt.simple_auth.auth');
    }

    public function testLogin()
    {
        $registeredUser = new User();
        $registeredUser->setEmail('test@test.ru');
        $registeredUser->setPassword('test_pass');

        /** @var \Zend\Authentication\AuthenticationService $auth */
        $auth = $this->getAuthenticationService();

        /** @var \DoctrineModule\Authentication\Adapter\ObjectRepository $adapter */
        $adapter = $auth->getAdapter();
        $adapter->setIdentityValue($registeredUser->getEmail());
        $adapter->setCredentialValue($registeredUser->getPassword());

        $result = $auth->authenticate();

        $this->assertFalse($result->isValid());

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $odm */
        $odm = $this->getDocumentManager();

        $odm->persist($registeredUser);

        $odm->flush();

        $odm->detach($registeredUser);

        $result = $auth->authenticate();

        $this->assertTrue($result->isValid());

        $this->assertEquals($auth->getIdentity()->getEmail(), $registeredUser->getEmail());
    }
}
