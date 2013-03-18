<?php
namespace ApptSimpleAuth\Plugin;

use ApptSimpleAuth\AuthService;
use ApptSimpleAuth\Zend\Form\Login;
use ApptSimpleAuth\Zend\Form\Logout;

abstract class AbstractAuth
{
    /**
     * @var AuthService
     */
    protected $auth;

    /**
     * @var Login
     */
    protected $loginForm;

    /**
     * @var Logout
     */
    protected $logoutForm;

    public function __construct(AuthService $auth, Login $loginForm, Logout $logoutForm)
    {
        $this->auth = $auth;
        $this->loginForm = $loginForm;
        $this->logoutForm = $logoutForm;
    }

    /**
     * @return Login
     */
    public function getLoginForm()
    {
        return $this->loginForm;
    }

    /**
     * @return Logout
     */
    public function getLogoutForm()
    {
        return $this->logoutForm;
    }

    /**
     * @return AuthService
     */
    protected function getAuth()
    {
        return $this->auth;
    }

    public function __invoke($acl = false)
    {
        $this->getAuth()->setAcl($acl);
        return $this;
    }

    /**
     * Checks if access allowed for some resource.
     * @see Auth::allowed
     */
    public function allowed()
    {
        $auth = $this->getAuth();

        return call_user_func_array(array($auth, 'allowed'), func_get_args());
    }
}