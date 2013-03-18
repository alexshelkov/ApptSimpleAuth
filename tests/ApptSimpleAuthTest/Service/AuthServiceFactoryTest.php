<?php
namespace ApptSimpleAuthTest\Service;

use PHPUnit_Framework_TestCase;

use ApptSimpleAuth\Service\AuthServiceFactory;

class AuthServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function getSmMock($config = array())
    {
        $odmName = isset($config['appt']['simple_auth']['documentManager']) ? $config['appt']['simple_auth']['documentManager'] : 'odm_default';

        $sm = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $sm->expects($this->at(0))->method('get')->with($this->equalTo('Config'))->will($this->returnValue($config));

        $authenticationService = $this->getMock('Zend\Authentication\AuthenticationService');
        $sm->expects($this->at(1))->method('get')->with($this->equalTo("doctrine.authenticationservice.$odmName"))->will($this->returnValue($authenticationService));

        $aclService = $this->getMock('ApptSimpleAuth\AclService', array(), array($this->getMock('Doctrine\Common\Persistence\ObjectRepository')));
        $sm->expects($this->at(2))->method('get')->with($this->equalTo('appt.simple_auth.acl'))->will($this->returnValue($aclService));

        return $sm;
    }

    public function testFactoryDefaults()
    {
        $factory = new AuthServiceFactory();

        $auth = $factory->createService($this->getSmMock());

        $this->assertInstanceOf('ApptSimpleAuth\AuthService', $auth);
    }

    public function testSetOdm()
    {
        $config['appt']['simple_auth']['documentManager'] = 'test_odm';

        $factory = new AuthServiceFactory();

        $auth = $factory->createService($this->getSmMock($config));

        $this->assertInstanceOf('ApptSimpleAuth\AuthService', $auth);
    }
}
