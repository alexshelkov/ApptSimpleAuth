<?php
namespace ApptSimpleAuth\Service\Options;

use Zend\Stdlib\AbstractOptions;

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
     * @var string
     */
    protected $documentManager = 'odm_default';

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

    /**
     * @param string $documentManager
     */
    public function setDocumentManager($documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @return string
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }


}