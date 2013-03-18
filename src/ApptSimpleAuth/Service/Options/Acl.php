<?php
namespace ApptSimpleAuth\Service\Options;

use Zend\Stdlib\AbstractOptions;
use Zend\ServiceManager\ServiceLocatorInterface;

class Acl extends AbstractOptions
{
    /**
     * @var string
     */
    protected $name = 'Acl';

    /**
     * @var string
     */
    protected $class = 'ApptSimpleAuth\Acl';

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    static public function init(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        if ( isset($config['appt']['simple_auth']['acl']) && is_array($config['appt']['simple_auth']['acl']) ) {
            $options = $config['appt']['simple_auth']['acl'];
        } else {
            $options = array();
        }

        return new self($options);
    }
}