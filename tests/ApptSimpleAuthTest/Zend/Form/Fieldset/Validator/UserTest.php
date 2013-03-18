<?php
namespace ApptSimpleAuthTest\Zend\Form\Fieldset\Validator;

use PHPUnit_Framework_TestCase;

use ApptSimpleAuth\Zend\Form\Fieldset\Validator\User;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;

class LogoutTest extends PHPUnit_Framework_TestCase
{
    protected function getUser(InputFilter $inputFilter = null)
    {
        $auth = $this->getMock('ApptSimpleAuth\AuthService', array(), array(), '', false);

        $user = new User(array('auth' => $auth));

        if ( $inputFilter ) {
            $user->setInputFilter($inputFilter);
        }

        return $user;
    }

    public function testBadOptions()
    {
         $this->setExpectedException(
            'ApptSimpleAuth\Zend\Form\Exception\RuntimeException',
            'Can\'t create ApptSimpleAuth\Zend\Form\Fieldset\Validator\User without auth'
        );

        new User();
    }

    public function testInvalidIsValueTrue()
    {
        $this->assertFalse($this->getUser()->isValid(User::FAIL));
    }

    public function testInvalidIfNoInputFilter()
    {
        $this->assertFalse($this->getUser()->isValid(0));
    }

    public function testInvalidBadInputFilter()
    {
        // no user
        $inputFilter = new InputFilter();
        $validator = $this->getUser($inputFilter);

        $this->assertSame($inputFilter, $validator->getInputFilter());
        $this->assertFalse($validator->isValid(0));

        // user not input filter
        $inputFilter = new InputFilter();
        $inputFilter->add(new Input(), 'user');

        $validator = $this->getUser($inputFilter);
        $this->assertFalse($validator->isValid(0));

        // no email or password
        $inputFilter = new InputFilter();
        $inputFilter->add(new InputFilter(), 'user');

        $validator = $this->getUser($inputFilter);
        $this->assertFalse($validator->isValid(0));
    }

    public function testInvalidIfNoEmailOrPassword()
    {
        $inputFilter = new InputFilter();

        $user = new InputFilter();
        $user->add(new Input(), 'email');
        $user->add(new Input(), 'password');

        $inputFilter->add($user, 'user');

        $validator = $this->getUser($inputFilter);
        $this->assertFalse($validator->isValid(0));
    }
}