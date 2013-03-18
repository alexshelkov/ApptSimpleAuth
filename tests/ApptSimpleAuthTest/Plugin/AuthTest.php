<?php
namespace ApptSimpleAuthTest\Plugin;

use PHPUnit_Framework_TestCase;

use ApptSimpleAuth\Plugin\AbstractAuth;

class AuthTest extends PHPUnit_Framework_TestCase
{
    public function testPlugin()
    {
        $authenticationService = $this->getMock('Zend\Authentication\AuthenticationService');
        $aclService = $this->getMock('ApptSimpleAuth\AclService', array(), array($this->getMock('Doctrine\Common\Persistence\ObjectRepository')));
        $auth = $this->getMock('ApptSimpleAuth\AuthService', array(), array($aclService, $authenticationService));

        $loginForm = $this->getMock('ApptSimpleAuth\Zend\Form\Login', array(), array(), '', false);
        $logoutForm = $this->getMock('ApptSimpleAuth\Zend\Form\Logout', array(), array(), '', false);

        /** @var AbstractAuth $plugin  */
        $plugin = $this->getMockForAbstractClass('ApptSimpleAuth\Plugin\AbstractAuth', array($auth, $loginForm, $logoutForm));

        $this->assertSame($loginForm, $plugin->getLoginForm());
        $this->assertSame($logoutForm, $plugin->getLogoutForm());

        $auth->expects($this->once())->method('setAcl')->with($this->equalTo('test_acl'));

        $this->assertEquals($plugin, $plugin('test_acl'));

        $auth->
            expects($this->once())->
            method('allowed')->
            with($this->equalTo('test_role'), $this->equalTo('test_resource'), $this->equalTo('test_rule'))->
            will($this->returnValue(true));

        $this->assertTrue($plugin->allowed('test_role', 'test_resource', 'test_rule'));
    }
}