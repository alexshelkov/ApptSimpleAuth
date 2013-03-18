<?php
namespace ApptSimpleAuthTest\Service\Zend\Form;

use ApptSimpleAuth\Service\Zend\Form\FieldSet\UserFactory;

use PHPUnit_Framework_TestCase;

use Zend\Form\FormElementManager;

class UserFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testBadSm()
    {
        $this->setExpectedException('ApptSimpleAuth\Service\Exception\InvalidArgumentException', 'Except instance of Zend\Form\FormElementManager got ');

        $authFactory = new UserFactory();

        $sm = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $authFactory->createService($sm);
    }

    protected function getSmMock($good = true)
    {
        $sm = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        if ( $good ) {
            $authenticationService = $this->getMock('Zend\Authentication\AuthenticationService');
            $aclService = $this->getMock('ApptSimpleAuth\AclService', array(), array($this->getMock('Doctrine\Common\Persistence\ObjectRepository')));
            $auth = $this->getMock('ApptSimpleAuth\AuthService', array(), array($aclService, $authenticationService));

            $sm->expects($this->at(0))->method('get')->with($this->equalTo('appt.simple_auth.auth'))->will($this->returnValue($auth));

            $loginForm = $this->getMock('ApptSimpleAuth\Zend\Form\Login', array(), array(), '', false);
            $logoutForm = $this->getMock('ApptSimpleAuth\Zend\Form\Logout', array(), array(), '', false);

            $formElementManager = $this->getMock('Zend\Form\FormElementManager', array(), array(), '', false);
            $formElementManager->expects($this->at(0))->method('get')->with($this->equalTo('appt.simple_auth.form.login'))->will($this->returnValue($loginForm));
            $formElementManager->expects($this->at(1))->method('get')->with($this->equalTo('appt.simple_auth.form.logout'))->will($this->returnValue($logoutForm));

            $sm->expects($this->at(1))->method('get')->with($this->equalTo('FormElementManager'))->will($this->returnValue($formElementManager));
            $sm->expects($this->at(2))->method('get')->with($this->equalTo('FormElementManager'))->will($this->returnValue($formElementManager));
        }

        return $sm;
    }

    public function testCreateHelper()
    {
        $formManager = new FormElementManager();

        $sm = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

//        $authenticationService = $this->getMock('Zend\Authentication\AuthenticationService');
//        $aclService = $this->getMock('ApptSimpleAuth\AclService', array(), array($this->getMock('Doctrine\Common\Persistence\ObjectRepository')));
        $auth = $this->getMock('ApptSimpleAuth\AuthService', array(), array(), '', false);

        $sm->expects($this->at(0))->method('get')->with($this->equalTo('appt.simple_auth.auth'))->will($this->returnValue($auth));

        $formManager->setServiceLocator($sm);

        $fieldSetFactory = new UserFactory();
        $userFieldset = $fieldSetFactory->createService($formManager);

        $this->assertInstanceOf('ApptSimpleAuth\Zend\Form\Fieldset\User', $userFieldset);
    }
}
