<?php
namespace ApptSimpleAuth\Zend\Form;

use ApptSimpleAuth\Zend\Form\AbstractForm;
use ApptSimpleAuth\Zend\Form\Fieldset\Validator\User;
use Zend\Stdlib\ParametersInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Http\Request as HttpRequest;
use ApptSimpleAuth\Zend\Form\Fieldset\Validator\User as UserValidator;
use ApptSimpleAuth\Zend\Form\Exception\DomainException;
use ApptSimpleAuth\Zend\Form\Exception\RuntimeException;
use Zend\Uri\Uri;

class Login extends AbstractForm
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var bool
     */
    protected $controllerDisplayEnable;

    /**
     * @return boolean
     */
    public function isControllerDisplayEnable()
    {
        return $this->controllerDisplayEnable;
    }

    protected function getCurrentUri()
    {
        $request = $this->request;

        if ( $request instanceof HttpRequest ) {
            return $request->getUriString();
        }

        return '';
    }

    /**
     * @return RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Based on forms requested URI generate fail URI by
     * adding auth-error and email as get params.
     *
     * @throws DomainException
     * @return null|string
     */
    public function getFailRedirectUri()
    {
        if ( ! $this->hasValidated ) {
            throw new DomainException(
                __METHOD__ . ' cannot get redirect uri as validation has not yet occurred'
            );
        }

        $data = $this->getData();

        if ( ! isset($data['fail_uri']) ) {
            throw new DomainException(
                __METHOD__ . ' cannot get redirect uri as no uri in data'
            );
        }

        $params = array('auth-error' => UserValidator::FAIL);

        if ( ! empty($data['user']['email']) ) {
            $params['email'] = $data['user']['email'];
        }

        $uri = new Uri($data['fail_uri']);

        $query = array_merge($uri->getQueryAsArray(), $params);

        $uri->setQuery($query);

        return $uri->toString();
    }

    public function init()
    {
        if ( $this->isInit ) {
            return;
        }

        parent::init();

        $this->add(array(
            'name' => 'fail_uri',
            'type' => 'text'
        ));

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
        $user->get('auth_error')->setValue(0);

        $this->get('fail_uri')->setValue($this->getCurrentUri());
        $this->get('submit')->setValue('Login');
    }

    public function __construct($name = null, $options = array())
    {
        $this->injectDependencies(array('request', 'controllerDisplayEnable' => 'controller_display_enable'), $options);

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

        if ( $this->isSetData() ) {
            /** @var ParametersInterface $query  */
            $query= $this->getRequest()->getQuery();

            $data['csrf'] = $this->get('csrf')->getValue();
            $data['user']['auth_error'] = $query['auth-error'];
            if ( isset($query['email']) ) {
                $data['user']['email'] = $query['email'];
            }

            $data = array_merge_recursive($data, (array)$this->data);
            $this->setData($data);
        }

        return parent::isValid();
    }

    protected function isSetData()
    {
        $request = $this->getRequest();
        return $request instanceof HttpRequest && $request->getQuery()->offsetExists('auth-error');
    }

    public function getViewModel()
    {
        if ( $this->isSetData() ) {
            $this->isValid();
        }

        return parent::getViewModel();
    }
}