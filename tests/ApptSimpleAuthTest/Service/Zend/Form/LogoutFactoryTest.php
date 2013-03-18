<?php
namespace ApptSimpleAuthTest\Service\Zend\Form;

use ApptSimpleAuth\Service\Zend\Form\LogoutFactory;

use PHPUnit_Framework_TestCase;

class LogoutFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testBadSm()
    {
        $this->setExpectedException('ApptSimpleAuth\Service\Exception\InvalidArgumentException', 'Except instance of Zend\Form\FormElementManager got ');

        $authFactory = new LogoutFactory();

        $sm = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $authFactory->createService($sm);
    }
}
