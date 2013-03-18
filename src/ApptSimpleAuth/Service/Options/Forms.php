<?php
namespace ApptSimpleAuth\Service\Options;

use Zend\Stdlib\AbstractOptions;
use Zend\ServiceManager\ServiceLocatorInterface;

class Forms extends AbstractOptions
{
    /**
     * @var string
     */
    protected $renderer = 'ViewRenderer';

    /**
     * @var string
     */
    protected $logoutSuccessRedirectParams;

    /**
     * @var string
     */
    protected $logoutTemplate = 'form/aauth/logout';

    /**
     * @var string
     */
    protected $loginSuccessRedirectParams;

    /**
     * @var string
     */
    protected $loginTemplate = 'form/aauth/login';

    protected function parseUrlOption($urlOption)
    {
        if ( is_array($urlOption) && isset($urlOption['route']) ) {
            $urlOption['params'] = (isset($urlOption['params']) && is_array($urlOption['params'])) ? $urlOption['params'] : array();
            $urlOption['options'] = (isset($urlOption['options']) && is_array($urlOption['options'])) ? $urlOption['options'] : array();
            $urlOption['reuse_matched_params'] = isset($urlOption['reuse_matched_params']) ? $urlOption['reuse_matched_params'] : false;
            return $urlOption;
        } elseif ( is_string($urlOption) ) {
            return $urlOption;
        }

        return null;
    }

    /**
     * @param string $renderer
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return string
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    public function setLogout(array $options)
    {
       $this->setLogoutSuccessRedirectParams(isset($options['success_redirect_params']) ? $this->parseUrlOption($options['success_redirect_params']) : null);
       $this->setLogoutTemplate(isset($options['template']) ? $options['template'] : $this->getLogoutTemplate());
    }

    public function setLogin(array $options)
    {
        $this->setLoginSuccessRedirectParams(isset($options['success_redirect_params']) ? $this->parseUrlOption($options['success_redirect_params']) : null);
        $this->setLoginTemplate(isset($options['template']) ? $options['template'] : $this->getLoginTemplate());
    }

    /**
     * @param string $logoutSuccessUrl
     */
    public function setLogoutSuccessRedirectParams($logoutSuccessUrl)
    {
        $this->logoutSuccessRedirectParams = $logoutSuccessUrl;
    }

    /**
     * @return string
     */
    public function getLogoutSuccessRedirectParams()
    {
        return $this->logoutSuccessRedirectParams;
    }

    /**
     * @param string $logoutTemplate
     */
    public function setLogoutTemplate($logoutTemplate)
    {
        $this->logoutTemplate = $logoutTemplate;
    }

    /**
     * @return string
     */
    public function getLogoutTemplate()
    {
        return $this->logoutTemplate;
    }

    /**
     * @param string $loginSuccessRedirectParams
     */
    public function setLoginSuccessRedirectParams($loginSuccessRedirectParams)
    {
        $this->loginSuccessRedirectParams = $loginSuccessRedirectParams;
    }

    /**
     * @return string
     */
    public function getLoginSuccessRedirectParams()
    {
        return $this->loginSuccessRedirectParams;
    }

    /**
     * @param string $loginTemplate
     */
    public function setLoginTemplate($loginTemplate)
    {
        $this->loginTemplate = $loginTemplate;
    }

    /**
     * @return string
     */
    public function getLoginTemplate()
    {
        return $this->loginTemplate;
    }

    static public function init(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        if ( isset($config['appt']['simple_auth']['forms']) && is_array($config['appt']['simple_auth']['forms']) ) {
            $options = $config['appt']['simple_auth']['forms'];
        } else {
            $options = array();
        }

        return new self($options);
    }
}