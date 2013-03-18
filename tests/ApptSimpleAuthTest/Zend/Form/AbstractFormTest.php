<?php
namespace ApptSimpleAuthTest\Zend\Form;

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
}
