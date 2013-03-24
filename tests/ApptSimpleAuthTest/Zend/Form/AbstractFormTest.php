<?php
namespace ApptSimpleAuthTest\Zend\Form;

use ApptSimpleAuth\Zend\Form\AbstractForm;
use PHPUnit_Framework_TestCase;

class AbstractFormTest extends PHPUnit_Framework_TestCase
{
    public function testBadOptionsRenderer()
    {
        $this->setExpectedException(
            'ApptSimpleAuth\Zend\Form\Exception\RuntimeException',
            'without renderer'
        );

        $this->getMockForAbstractClass('ApptSimpleAuth\Zend\Form\AbstractForm', array());
    }

    public function testBadOptionsTemplate()
    {
        $this->setExpectedException(
            'ApptSimpleAuth\Zend\Form\Exception\RuntimeException',
            'without template'
        );

        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');

        $this->getMockForAbstractClass('ApptSimpleAuth\Zend\Form\AbstractForm', array(null, array('renderer' => $renderer)));
    }

    public function testBadOptionsRouter()
    {
        $this->setExpectedException(
            'ApptSimpleAuth\Zend\Form\Exception\RuntimeException',
            'without router'
        );

        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');

        $this->getMockForAbstractClass('ApptSimpleAuth\Zend\Form\AbstractForm', array(null, array('renderer' => $renderer, 'template' => 'test')));
    }

    public function testGetSuccessRedirectUriNoData()
    {
        $this->setExpectedException(
            'ApptSimpleAuth\Zend\Form\Exception\DomainException',
            'cannot get redirect uri as validation has not yet occurred'
        );

        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');
        $router = $this->getMock('Zend\Mvc\Router\RouteInterface');

        /** @var AbstractForm $form  */
        $form = $this->getMockForAbstractClass('ApptSimpleAuth\Zend\Form\AbstractForm', array(null, array('renderer' => $renderer, 'template' => 'test', 'router' => $router, 'success_redirect_params' => 'test')));

        $form->getSuccessRedirectUri();
    }

    public function testGetSuccessRedirectUriNoUriInData()
    {
        $this->setExpectedException(
            'ApptSimpleAuth\Zend\Form\Exception\DomainException',
            'cannot get redirect uri as no uri in data'
        );

        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');
        $router = $this->getMock('Zend\Mvc\Router\RouteInterface');

        /** @var AbstractForm $form */
        $form = $this->getMockForAbstractClass('ApptSimpleAuth\Zend\Form\AbstractForm', array(null, array('renderer' => $renderer, 'template' => 'test', 'router' => $router, 'success_redirect_params' => 'test')));

        $form->setData(array());

        $form->isValid();

        $form->getSuccessRedirectUri();
    }

    public function testSetSuccessRedirectUriNoInit()
    {
        $this->setExpectedException(
            'ApptSimpleAuth\Zend\Form\Exception\DomainException',
            'cannot set redirect uri as form not init yet'
        );

        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');
        $router = $this->getMock('Zend\Mvc\Router\RouteInterface');

        /** @var AbstractForm $form */
        $form = $this->getMockForAbstractClass('ApptSimpleAuth\Zend\Form\AbstractForm', array(null, array('renderer' => $renderer, 'template' => 'test', 'router' => $router, 'success_redirect_params' => 'test')));

        $form->setSuccessRedirectUri('test');
    }
}
