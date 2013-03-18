<?php
namespace ApptSimpleAuth\Acl;

use SimpleAcl\Resource as AclResource;

use ApptSimpleAuth\Acl\Object\RecursiveIterator;
use Doctrine\Common\Collections\ArrayCollection;

class Resource extends AclResource
{
    public function getIterator()
    {
        return new RecursiveIterator(array($this));
    }

    public function __construct($name)
    {
        $this->objects = new ArrayCollection();
        parent::__construct($name);
    }
}
