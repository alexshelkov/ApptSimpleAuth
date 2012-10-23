<?php
namespace ApptSimpleAuth;

use SimpleAcl\Acl as SimpleAcl;
use Doctrine\Common\Collections\ArrayCollection;

class Acl extends SimpleAcl
{
    protected $name;

    public function __construct($name)
    {
        $this->rules = new ArrayCollection;
        $this->setName($name);
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function removeAllRules()
    {
        $this->rules->clear();
    }
}