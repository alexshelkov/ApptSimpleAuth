<?php
namespace ApptSimpleAuthTest\Zend\Form;

use PHPUnit_Framework_TestCase;
use ApptSimpleAuth\Zend\Form\Login;
use Zend\Form\FormElementManager;
use Zend\ServiceManager\Config;

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
        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');
        $router = $this->getMock('Zend\Mvc\Router\RouteInterface');

        $login = new Login(null, array('request' => $request, 'template' => 'test', 'renderer' => $renderer, 'router' => $router,));

        $login->getFailRedirectUri();
    }

    public function testGetFailUriWithNoUri()
    {
        $this->setExpectedException(
            'ApptSimpleAuth\Zend\Form\Exception\DomainException',
            'ApptSimpleAuth\Zend\Form\Login::getFailRedirectUri cannot get redirect uri as no uri in data'
        );

        $request = $this->getMock('Zend\Stdlib\RequestInterface');
        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');
        $router = $this->getMock('Zend\Mvc\Router\RouteInterface');

        $login = new Login(null, array('request' => $request, 'template' => 'test', 'renderer' => $renderer, 'router' => $router,));
        $login->setData(array());
        $login->isValid();

        $login->getFailRedirectUri();
    }
}
