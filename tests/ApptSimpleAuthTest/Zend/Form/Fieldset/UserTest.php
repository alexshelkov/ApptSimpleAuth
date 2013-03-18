<?php
namespace ApptSimpleAuthTest\Zend\Form\Fieldset;

use PHPUnit_Framework_TestCase;

use ApptSimpleAuth\Zend\Form\Fieldset\User;

class LogoutTest extends PHPUnit_Framework_TestCase
{
    public function testBadOptions()
    {
         $this->setExpectedException(
            'ApptSimpleAuth\Zend\Form\Exception\RuntimeException',
            'Can\'t create ApptSimpleAuth\Zend\Form\Fieldset\User without auth'
        );

        new User();
    }
}