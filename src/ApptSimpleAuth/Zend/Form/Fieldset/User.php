<?php
namespace ApptSimpleAuth\Zend\Form\Fieldset;

use ApptSimpleAuth\AuthService;

use Zend\Form\Fieldset as ZendFieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use ApptSimpleAuth\Zend\Form\Fieldset\Validator\User as UserValidator;
use Zend\Validator\Callback;
use ApptSimpleAuth\Zend\Form\Exception\RuntimeException;

class User extends ZendFieldset implements InputFilterProviderInterface
{
    /**
     * @var AuthService
     */
    protected $auth;

    /**
     * @var UserValidator
     */
    protected $validator;

    /**
     * @return AuthService
     */
    protected function getAuth()
    {
        return $this->auth;
    }

    public function init()
    {
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'text',
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'text',
            ),
        ));

        $this->add(array(
            'name' => 'auth_error',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
    }

    public function __construct($name = null, $options = array())
    {
        if ( empty($options['auth']) || (isset($options['auth']) && ! $options['auth'] instanceof AuthService) ) {
            throw new RuntimeException('Can\'t create ' . get_called_class() . ' without auth');
        }

        $this->auth = $options['auth'];
        $name = $name ? ! $name : 'user';
        parent::__construct($name, $options);
    }

    public function getValidator()
    {
        if ( ! $this->validator ) {
            $validator = new UserValidator(array('auth' => $this->getAuth()));
            $this->validator = $validator;
        }

        return $this->validator;
    }

    public function getInputFilterSpecification()
    {
        return array(
            'email' => array(
                'required' => false,
                'filters' => array(
                     array('name' => 'Zend\Filter\StringTrim'),
                     array('name' => 'Zend\Filter\StringToLower'),
                 ),
                'validators' => array(
                    array('name' => 'EmailAddress', 'break_chain_on_failure' => true),
                )
            ),
            'password' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StringTrim'),
                ),
            ),
            'auth_error' => array(
                'required' => false,
                'validators' => array(
                    $this->getValidator()
                )
            )
        );
    }
}