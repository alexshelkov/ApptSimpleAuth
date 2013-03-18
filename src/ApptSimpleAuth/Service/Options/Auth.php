<?php
namespace ApptSimpleAuth\Service\Options;

use Zend\Stdlib\AbstractOptions;
use Zend\ServiceManager\ServiceLocatorInterface;

class Auth extends AbstractOptions
{
    /**
     * @var string
     */
    protected $documentManager = 'odm_default';

    /**
     * @var string
     */
    protected $errorTemplate;

    /**
     * @param string $documentManager
     */
    public function setDocumentManager($documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @param string $errorTemplate
     */
    public function setErrorTemplate($errorTemplate)
    {
        $this->errorTemplate = $errorTemplate;
    }

    /**
     * @return string
     */
    public function getErrorTemplate()
    {
        return $this->errorTemplate;
    }

    /**
     * @return string
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    static public function init(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        if ( isset($config['appt']['simple_auth']) && is_array($config['appt']['simple_auth']) ) {
            unset($config['appt']['simple_auth']['acl']);
            unset($config['appt']['simple_auth']['forms']);
            $options = $config['appt']['simple_auth'];
        } else {
            $options = array();
        }

        return new self($options);
    }
}