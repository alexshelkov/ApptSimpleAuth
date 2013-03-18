<?php
namespace ApptSimpleAuthTest\Service\Zend\View\Helper;

use ApptSimpleAuth\Service\Zend\View\Helper\AuthFactory;

use Zend\View\HelperPluginManager;
use ApptSimpleAuthTest\Service\Zend\Controller\Plugin\AuthFactoryTest as BaseTest;

class AuthFactoryTest extends BaseTest
{
    public function testBadSm()
    {
        $this->setExpectedException('ApptSimpleAuth\Service\Exception\InvalidArgumentException', 'Except instance of Zend\View\HelperPluginManager got ');

        $authFactory = new AuthFactory();

        $authFactory->createService($this->getSmMock(false));
    }

    public function testCreateHelper()
    {
        $helperPluginManger = new HelperPluginManager();

        $sm = $this->getSmMock();

        $helperPluginManger->setServiceLocator($sm);

        $authFactory = new AuthFactory();

        $authHelper = $authFactory->createService($helperPluginManger);

        $this->assertInstanceOf('ApptSimpleAuth\Zend\View\Helper\Auth', $authHelper);
    }
}
