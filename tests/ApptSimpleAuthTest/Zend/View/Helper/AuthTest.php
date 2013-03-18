<?php
namespace ApptSimpleAuthTest\Zend\View\Helper;

use PHPUnit_Framework_TestCase;

use ApptSimpleAuth\Zend\View\Helper\Auth;

class AuthTest extends PHPUnit_Framework_TestCase
{
    public function testHelper()
    {
        $auth = $this->getMock('ApptSimpleAuth\AuthService', array(), array(), '', false);
        $loginForm = $this->getMock('ApptSimpleAuth\Zend\Form\Login', array(), array(), '', false);
        $logoutForm = $this->getMock('ApptSimpleAuth\Zend\Form\Logout', array(), array(), '', false);

        $helper = new Auth($auth, $loginForm, $logoutForm);

        $viewMock = $this->getMock('Zend\View\Renderer\RendererInterface');

        $helper->setView($viewMock);
        $this->assertEquals($viewMock, $helper->getView());
    }
}
