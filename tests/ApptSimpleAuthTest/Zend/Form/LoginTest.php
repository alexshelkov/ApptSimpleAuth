<?php
namespace ApptSimpleAuthTest\Zend\Form;

use PHPUnit_Framework_TestCase;
use ApptSimpleAuth\Zend\Form\Login;

class LoginTest extends PHPUnit_Framework_TestCase
{
    public function testBadOptionsRequest()
    {
        $this->setExpectedException(
            'ApptSimpleAuth\Zend\Form\Exception\RuntimeException',
            'Can\'t create ApptSimpleAuth\Zend\Form\Login without request'
        );

        new Login();
    }

    public function testGetFailUriBeforeValidation()
    {
        $this->setExpectedException(
            'ApptSimpleAuth\Zend\Form\Exception\DomainException',
            'ApptSimpleAuth\Zend\Form\Login::getFailRedirectUri cannot get redirect uri as validation has not yet occurred'
        );

        $request = $this->getMock('Zend\Stdlib\RequestInterface');
        $template = 'test';
        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');

        $login = new Login(null, array('request' => $request, 'template' => $template, 'renderer' => $renderer));

        $login->getFailRedirectUri();
    }

    public function testSetFailUri()
    {
        $request = $this->getMock('Zend\Stdlib\RequestInterface');
        $template = 'test';
        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');

        $login = new Login(null, array('request' => $request, 'template' => $template, 'renderer' => $renderer));

        $login->setFailRedirectUri('test');

        $this->assertEquals('test', $login->getFailRedirectUri());
    }
}
