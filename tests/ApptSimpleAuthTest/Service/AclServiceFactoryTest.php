<?php
namespace ApptSimpleAuthTest\Service;

use PHPUnit_Framework_TestCase;

use ApptSimpleAuth\Service\AclServiceFactory;

class AclServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function getSmMock($config = array(), $good = true)
    {
        $sm = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $sm->expects($this->at(0))->method('get')->with($this->equalTo('Config'))->will($this->returnValue($config));
        $sm->expects($this->at(1))->method('get')->with($this->equalTo('Config'))->will($this->returnValue($config));

        if ( $good ) {
            $omd = $this->getMock('Doctrine\ODM\MongoDB\DocumentManager', array(), array(), '', false);
            $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
            $omd->expects($this->once())->method('getRepository')->with($this->equalTo('ApptSimpleAuth\Acl'))->will($this->returnValue($repository));

            $sm->expects($this->at(2))->method('get')->with($this->equalTo('doctrine.documentmanager.odm_default'))->will($this->returnValue($omd));
        }

        return $sm;
    }

    public function testFactoryDefaults()
    {
        $factory = new AclServiceFactory();

        $aclService = $factory->createService($this->getSmMock());

        $this->assertEquals('Acl', $aclService->getDefaultAcl());
    }

    public function testBadName()
    {
        $this->setExpectedException('ApptSimpleAuth\Service\Exception\RuntimeException', "Acl name can't be empty");

        $factory = new AclServiceFactory();

        $config['appt']['simple_auth']['acl']['name'] = '';

        $sm = $this->getSmMock($config, false);

        $factory->createService($sm);
    }

    public function testGoodName()
    {
        $factory = new AclServiceFactory();

        $config['appt']['simple_auth']['acl']['name'] = 'Test';

        $sm = $this->getSmMock($config);

        $aclService = $factory->createService($sm);

        $this->assertEquals('Test', $aclService->getDefaultAcl());
    }

    public function testNotExistingClass()
    {
        $this->setExpectedException('ApptSimpleAuth\Service\Exception\RuntimeException', 'is undefined');

        $factory = new AclServiceFactory();

        $config['appt']['simple_auth']['acl']['class'] = 'not_existing_class_' . uniqid();

        $sm = $this->getSmMock($config, false);

        $factory->createService($sm);
    }

    public function testBadClass()
    {
        $this->setExpectedException('ApptSimpleAuth\Service\Exception\RuntimeException', 'stdClass don\'t extends ApptSimpleAuth\\Acl');

        $factory = new AclServiceFactory();

        $config['appt']['simple_auth']['acl']['class'] = 'stdClass';

        $sm = $this->getSmMock($config, false);

        $factory->createService($sm);
    }
}
