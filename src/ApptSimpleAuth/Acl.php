<?php
namespace ApptSimpleAuth;

use SimpleAcl\Acl as SimpleAcl;
use Doctrine\Common\Collections\ArrayCollection;
use RecursiveIteratorIterator;

use Zend\Permissions\Acl\AclInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class Acl extends SimpleAcl implements AclInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->rules = new ArrayCollection;
        $this->name = $name;
    }

    public function hasResource($resourceName)
    {
        if ( $resourceName instanceof ResourceInterface ) {
            $resourceName = $resourceName->getResourceId();
        }

        foreach ( $this->rules as $rule ) {
            if ( $resources = $rule->getResource() ) {
                $resources = new RecursiveIteratorIterator($resources, RecursiveIteratorIterator::SELF_FIRST);
                foreach ($resources as $resource) {
                    if ( $resource->getName() == $resourceName ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function isAllowed($role = null, $resource = null, $rule = null)
    {
        if ( $role instanceof RoleInterface ) {
            $role = $role->getRoleId();
        }

        if ( $resource instanceof ResourceInterface ) {
            $resource = $resource->getResourceId();
        }

        return parent::isAllowed($role, $resource, $rule);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function removeAllRules()
    {
        $this->rules->clear();
    }
}