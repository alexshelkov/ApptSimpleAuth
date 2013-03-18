<?php
namespace ApptSimpleAuthTest\Zend\Controller\Plugin;

use PHPUnit_Framework_TestCase;

use ApptSimpleAuth\Zend\Controller\Plugin\Auth;

class AuthTest extends PHPUnit_Framework_TestCase
{
    public function testPlugin()
    {
        $auth = $this->getMock('ApptSimpleAuth\AuthService', array(), array(), '', false);
        $loginForm = $this->getMock('ApptSimpleAuth\Zend\Form\Login', array(), array(), '', false);
        $logoutForm = $this->getMock('ApptSimpleAuth\Zend\Form\Logout', array(), array(), '', false);

        $plugin = new Auth($auth, $loginForm, $logoutForm);

        $controller = $this->getMock('Zend\Stdlib\DispatchableInterface');

        $plugin->setController($controller);

        $this->assertEquals($controller, $plugin->getController());
    }
}
