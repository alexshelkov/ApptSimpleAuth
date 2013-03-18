<?php
namespace ApptSimpleAuthTest\Service;

use PHPUnit_Framework_TestCase;

use ApptSimpleAuth\Service\ManagerServiceFactory;

class ManagerFactoryTest extends PHPUnit_Framework_TestCase
{
    public function getSmMock($config = array(), $good = true)
    {
        $odmName = isset($config['appt']['simple_auth']['documentManager']) ? $config['appt']['simple_auth']['documentManager'] : 'odm_default';

        $sm = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $sm->expects($this->at(0))->method('get')->with($this->equalTo('Config'))->will($this->returnValue($config));

        if ( $good ) {
            $omd = $this->getMock('Doctrine\ODM\MongoDB\DocumentManager', array(), array(), '', false);
            $sm->expects($this->at(1))->method('get')->with($this->equalTo("doctrine.documentmanager.$odmName"))->will($this->returnValue($omd));

            $acl = $this->getMock('ApptSimpleAuth\AclService', array(), array($this->getMock('Doctrine\Common\Persistence\ObjectRepository')));
            $sm->expects($this->at(2))->method('get')->with($this->equalTo('appt.simple_auth.acl'))->will($this->returnValue($acl));
        }

        return $sm;
    }

    public function testFactoryDefaults()
    {
        $factory = new ManagerServiceFactory();

        $sm = $this->getSmMock();

        $manager = $factory->createService($sm);

        $this->assertInstanceOf('ApptSimpleAuth\ManagerService', $manager);
    }

    public function testConfig()
    {
        $factory = new ManagerServiceFactory();

        $config['appt']['simple_auth']['documentManager'] = 'odm_test';

        $sm = $this->getSmMock($config);

        $manager = $factory->createService($sm);

        $this->assertInstanceOf('ApptSimpleAuth\ManagerService', $manager);
    }
}
