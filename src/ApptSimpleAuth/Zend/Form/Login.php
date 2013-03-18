<?php
namespace ApptSimpleAuth\Zend\Form;

use ApptSimpleAuth\Zend\Form\AbstractForm;
use ApptSimpleAuth\Zend\Form\Fieldset\Validator\User;
use Zend\Stdlib\RequestInterface;
use Zend\Http\Request as HttpRequest;
use ApptSimpleAuth\Zend\Form\Fieldset\Validator\User as UserValidator;
use ApptSimpleAuth\Zend\Form\Exception\DomainException;
use ApptSimpleAuth\Zend\Form\Exception\RuntimeException;

class Login extends AbstractForm
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    protected $failRedirectUri;

    /**
     * @param $failRedirectUri
     */
    public function setFailRedirectUri($failRedirectUri)
    {
        $this->failRedirectUri = $failRedirectUri;
    }

    /**
     * @return RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Based on current requested URI generate fail URI by
     * adding auth-error and email as get params.
     *
     * @throws DomainException
     * @return null|string
     */
    public function getFailRedirectUri()
    {
        if ( ! $this->failRedirectUri ) {
            if ( ! $this->hasValidated ) {
                throw new DomainException(sprintf(
                    '%s cannot get redirect uri as validation has not yet occurred',
                    __METHOD__
                ));
            }

            $request = $this->getRequest();
            if ( $request instanceof HttpRequest ) {

                $data = $this->getData();

                $params = array('auth-error' => UserValidator::FAIL);

                if ( ! empty($data['user']['email']) ) {
                    $params['email'] = $data['user']['email'];
                }

                $uri = $request->getUri();

                $query = array_merge($uri->getQueryAsArray(), $params);

                $uri->setQuery($query);

                $this->failRedirectUri = $uri->toString();
            }
        }

        return $this->failRedirectUri;
    }

    public function init()
    {
        if ( $this->isInit ) {
            return;
        }

        parent::init();

        // must be added as last form element
        $this->add(array(
            'name' => 'user',
            'type' => 'appt.simple_auth.form.fieldset.user'
        ));

        /** @var \ApptSimpleAuth\Zend\Form\Fieldset\User $user  */
        $user = $this->get('user');
        // users validator should be aware of input filter, so it can check if some
        // prev. validations failed and always be invalid in this case
        $user->getValidator()->setInputFilter($this->getInputFilter());

        $this->get('submit')->setValue('Login');
    }

    public function __construct($name = null, $options = array())
    {
        $this->injectDependencies(array('request'), $options);

        if ( ! $this->request instanceof RequestInterface ) {
            throw new RuntimeException('Can\'t create ' . get_called_class() . ' without request');
        }

        $name = ! $name ? 'login' : $name;
        parent::__construct($name, $options);
    }

    public function isValid()
    {
        if ( $this->hasValidated ) {
            return $this->isValid;
        }

        $request = $this->getRequest();

        if ( $this->isSetData() ) {
            $data = array(
                'csrf' => $this->get('csrf')->getValue(),
                'user' => array(
                    'auth_error' => $request->getQuery('auth-error')
                )
            );

            if ( $request->getQuery()->offsetExists('email') ) {
                $data['user']['email'] = $request->getQuery('email');
            }

            $this->setData($data);
        } else {
            if ( ! isset($this->data['user']['auth_error']) ) {
                $this->data['user']['auth_error'] = 0;
            }
        }

        return parent::isValid();
    }

    protected function isSetData()
    {
        $request = $this->getRequest();
        return $request instanceof HttpRequest && $request->getQuery()->offsetExists('auth-error') && ! $this->data;
    }

    public function getViewModel()
    {
        if ( $this->isSetData() ) {
            $this->isValid();
        }

        return parent::getViewModel();
    }
}