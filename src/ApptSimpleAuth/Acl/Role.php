<?php
namespace ApptSimpleAuth\Acl;

use SimpleAcl\Role as AclRole;

use ApptSimpleAuth\Acl\Object\RecursiveIterator;
use Doctrine\Common\Collections\ArrayCollection;

class Role extends AclRole
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