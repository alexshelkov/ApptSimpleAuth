<?php
namespace ApptSimpleAuth\Zend\Form\Fieldset\Validator;

use ApptSimpleAuth\AuthService;
use Zend\Validator\AbstractValidator;
use Zend\InputFilter\InputFilterInterface;
use Zend\Authentication\Result;
use ApptSimpleAuth\Zend\Form\Exception\RuntimeException;

/**
 * Used to validate user fieldset, and in case that
 * all inputs in fieldset are valid try to authenticate
 * the user.
 *
 */
class User extends AbstractValidator
{
    const FAIL = 'fail';

    /**
     * @var InputFilterInterface
     */
    protected $inputFilter;

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::FAIL => 'Incorrect email or password provided'
    );

    /**
     * @var AuthService
     */
    protected $auth;

    /**
     * @param InputFilterInterface $inputFilter
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    /**
     * Check if input filter is configured correctly (it should compose
     * user as an input filter).
     *
     * @return bool
     */
    protected function isGoodInputFilter()
    {
        if ( ! $this->getInputFilter() instanceof InputFilterInterface ) {
            return false;
        }

        if ( ! $this->getInputFilter()->has('user') ) {
            return false;
        }

        $user = $this->getInputFilter()->get('user');

        if ( !$user instanceof InputFilterInterface) {
            return false;
        }

        if ( ! $user->has('email') || ! $user->has('password') ) {
            return false;
        }

        return true;
    }

    /**
     * Check if any elements in input filter are invalid.
     *
     * @return bool
     */
    protected function isValidInputFilter()
    {
        if ( ! $this->isGoodInputFilter() ) {
            return false;
        }

        $i = $this->getInputFilter();

        return ! ( $i->getInvalidInput() || $i->get('user')->getInvalidInput() );
    }

    /**
     * Get email from input filter.
     *
     * @return string
     */
    protected function getEmail()
    {
        return $this->getInputFilter()->get('user')->getValue('email');
    }

    /**
     * Get password from input filter.
     *
     * @return string
     */
    protected function getPassword()
    {
        return $this->getInputFilter()->get('user')->getValue('password');
    }

    /**
     * @return AuthService
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param null $options
     *
     * @throws RuntimeException
     */
    public function __construct($options = null)
    {
        if ( empty($options['auth']) || (isset($options['auth']) && ! $options['auth'] instanceof AuthService) ) {
            throw new RuntimeException('Can\'t create ' . get_called_class() . ' without auth');
        }

        $this->auth = $options['auth'];
        unset($options['auth']);
        parent::__construct();
    }

    /**
     * Checks if authentication allowed by given email and password.
     *
     * Password and email retrieved from input filter.
     *
     * If input filter contains invalid inputs method also fails.
     *
     * @param string $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        if ( $value ) {
            $this->error($value);
            return false;
        }

        if ( ! $this->isValidInputFilter() ) {
            $this->error(self::FAIL);
            return false;
        }

        if ( ! $this->getEmail() || ! $this->getPassword() ) {
            $this->error(self::FAIL);
            return false;
        }

        $result = $this->getAuth()->authenticate($this->getEmail(), $this->getPassword());


        if ( ! $result->isValid() ) {
            $this->error(self::FAIL);
        }
        
        return $result->isValid();
    }
}
