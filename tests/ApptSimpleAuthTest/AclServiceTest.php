<?php
namespace ApptSimpleAuthTest;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

use ApptSimpleAuth\AclService;

class AclServiceTest extends AbstractConsoleControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(include TEST_APPLICATION_CONFIG);
        parent::setUp();
    }

    public function getAclMock($name)
    {
        $acl = $this->getMock('ApptSimpleAuth\Acl', array(), array($name));

        $acl->expects($this->any())->method('getName')->will($this->returnValue($name));

        return $acl;
    }

    public function testAclService()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $aclService = new AclService($repository);

        $aclService->setDefaultAcl('test_default');

        $this->assertEquals('test_default', $aclService->getDefaultAcl());
    }

    public function testGetDefaultAcl()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository->expects($this->once())->method('find')->will($this->returnValue($this->getAclMock('test_acl')));

        $aclService = new AclService($repository);

        $aclService->setDefaultAcl('test_default');

        $this->assertEquals('test_acl', $aclService->getAcl()->getName());
    }

    public function testGetNotDefaultAcl()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository->expects($this->once())->method('find')->will($this->returnValue($this->getAclMock('test_not_default')));

        $aclService = new AclService($repository);

        $aclService->setDefaultAcl('test_default');
        $this->assertEquals('test_default', $aclService->getDefaultAcl());

        $this->assertEquals('test_not_default', $aclService->getAcl('test_not_default')->getName());
    }

    public function testCreateAclGet()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository->expects($this->once())->method('find')->will($this->returnValue($this->getAclMock('test_default')));

        $aclService = new AclService($repository);

        $aclService->setDefaultAcl('test_default');

        $this->assertEquals('test_default', $aclService->createAcl()->getName());
    }

    public function testCreateAclDefault()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository->expects($this->once())->method('find')->will($this->returnValue(false));
        $repository->expects($this->once())->method('getClassName')->will($this->returnValue('ApptSimpleAuth\Acl'));

        $aclService = new AclService($repository);

        $aclService->setDefaultAcl('test_default');

        $this->assertEquals('test_default', $aclService->createAcl()->getName());
    }

    public function testCreateAcl()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository->expects($this->once())->method('find')->will($this->returnValue(false));
        $repository->expects($this->once())->method('getClassName')->will($this->returnValue('ApptSimpleAuth\Acl'));

        $aclService = new AclService($repository);

        $aclService->setDefaultAcl('test_default');

        $this->assertEquals('test_created', $aclService->createAcl('test_created')->getName());
    }
}
