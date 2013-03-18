<?php
namespace ApptSimpleAuth;

use Doctrine\ODM\MongoDB\DocumentManager;

use ApptSimpleAuth\Exception\RuntimeException;

use ApptSimpleAuth\AclService;
use ApptSimpleAuth\User;
use ApptSimpleAuth\Acl\Role;
use ApptSimpleAuth\Acl\Resource;
use SimpleAcl\Rule;

class ManagerService
{
    const ROLE_CLASS = 'ApptSimpleAuth\Acl\Role';
    const RESOURCE_CLASS = 'ApptSimpleAuth\Acl\Resource';
    const RULE_CLASS = 'SimpleAcl\Rule';
    const USER_CLASS = 'ApptSimpleAuth\User';
    const ACL_CLASS = 'ApptSimpleAuth\Acl';

    protected $shortcuts = array(
        'acl' => self::ACL_CLASS,
        'user' => self::USER_CLASS,
        'role' => self::ROLE_CLASS,
        'resource' => self::RESOURCE_CLASS,
        'rule' => self::RULE_CLASS
    );

    /**
     * @var AclService
     */
    protected $aclService;

    /**
     * @var DocumentManager
     */
    protected $odm;

    /**
     * @param DocumentManager $odm
     * @param AclService $aclService
     */
    public function __construct(DocumentManager $odm, AclService $aclService)
    {
        $this->aclService = $aclService;
        $this->odm = $odm;
    }

    /**
     * @param string $shortcut
     *
     * @return string
     * @throws RuntimeException
     */
    protected function getClassFromShortcuts($shortcut)
    {
        if ( isset($this->shortcuts[$shortcut]) ) {
            return $this->shortcuts[$shortcut];
        }

        throw new RuntimeException('Invalid shortcut given');
    }

    /**
     * @return DocumentManager
     */
    public function getOdm()
    {
        return $this->odm;
    }

    /**
     * @return AclService
     */
    public function getAclService()
    {
        return $this->aclService;
    }

    /**
     * @return array
     */
    public function clearDb()
    {
        $clear = array(self::ROLE_CLASS, self::RESOURCE_CLASS, self::RULE_CLASS, self::USER_CLASS, self::ACL_CLASS);

        $dbs = array();
        foreach ( $clear as $class ) {
            $collection = $this->getOdm()->getDocumentCollection($class);
            $collection->remove(array(), array('safe' => true));
            $this->getOdm()->clear($class);

            $dbs[] = $collection->db;
        }

        return array_unique($dbs);
    }

    /**
     * @param string | bool $name
     *
     * @return Acl
     */
    public function addAcl($name)
    {
        if ( ! $acl = $this->getAclService()->getAcl($name) ) {
            $acl = $this->getAclService()->createAcl($name, false);
            $this->getOdm()->persist($acl);
            $this->getOdm()->flush($acl);
        }

        return $acl;
    }

    /**
     * @param $email
     * @param string | null $pass
     * @param string | null $roleName
     *
     * @return User
     */
    public function addUser($email, $pass = null, $roleName = null)
    {
        if ( ! $user = $this->getOdm()->getRepository(self::USER_CLASS)->findOneBy(array('email' => $email)) ) {
            $user = new User();
            $user->setEmail($email);
        }

        if ( $pass != null ) {
            $user->setPassword($pass);
        }

        if ( $roleName ) {
            $role = $this->addRole($roleName);
            $user->addRole($role);
        }

        $this->getOdm()->persist($user);
        $this->getOdm()->flush();

        return $user;
    }

    /**
     * @param $name
     * @param string | null $childName
     *
     * @return Role
     */
    public function addRole($name, $childName = null)
    {
        if ( ! $role = $this->getOdm()->find(self::ROLE_CLASS, $name) ) {
            $role = new Role($name);
        }

        if ( $childName ) {
            $child = $this->getOdm()->find(self::ROLE_CLASS, $childName);

            if ( ! $child ) {
                $child = new Role($childName);
            }
            $role->addChild($child);
        }

        $this->getOdm()->persist($role);
        $this->getOdm()->flush();

        return $role;
    }

    /**
     * @param string $name
     * @param string | null $childName
     *
     * @return Resource
     */
    public function addResource($name, $childName = null)
    {
        if ( ! $resource = $this->getOdm()->find(self::RESOURCE_CLASS, $name) ) {
            $resource = new Resource($name);
        }

        if ( $childName ) {
            $child = $this->getOdm()->find(self::RESOURCE_CLASS, $childName);
            if ( ! $child ) {
                $child = new Resource($childName);
            }
            $resource->addChild($child);
        }

        $this->getOdm()->persist($resource);
        $this->getOdm()->flush();

        return $resource;
    }

    /**
     * @param string $ruleName
     * @param string $roleName
     * @param string $resourceName
     * @param bool $allow
     * @param string | bool $acl
     *
     * @return Acl
     */
    public function addRule($ruleName, $roleName, $resourceName, $allow = true, $acl = false)
    {
        $role = $this->addRole($roleName);
        $resource = $this->addResource($resourceName);

        $acl = $this->addAcl($acl);

        $rule = new Rule($ruleName);

        $acl->addRule($role, $resource, $rule, $allow);

        $this->getOdm()->persist($acl);
        $this->getOdm()->flush();

        return $acl;
    }

    /**
     * @param string $what
     * @param string $name
     * @param $useRuleName
     *
     * @return bool
     */
    public function remove($what, $name, $useRuleName = true)
    {
        $class = $this->getClassFromShortcuts($what);

        $findById = array(self::ACL_CLASS, self::ROLE_CLASS, self::RESOURCE_CLASS);
        if ( in_array($class, $findById) || ($class == self::RULE_CLASS && ! $useRuleName) ) {
            $object = $this->getOdm()->find($class, $name);
        } elseif ( $class == self::RULE_CLASS ) {
            $object = $this->getOdm()->getRepository($class)->findOneBy(array('name' => $name));
        } else {
            $object = $this->getOdm()->getRepository($class)->findOneBy(array('email' => $name));
        }

        if ( $object ) {
            if ( $class == self::ROLE_CLASS || $class == self::RESOURCE_CLASS ) {
                $parents = $this->getOdm()->getRepository($class)->findBy(array('children' => $name));
                foreach ( $parents as $parent ) {
                    $parent->removeChild($object);
                    $this->getOdm()->persist($parent);
                }
            } elseif ( $class == self::RULE_CLASS ) {
                /** @var Acl $acl  */
                $acls = $this->getOdm()->getRepository(self::ACL_CLASS)->findBy(array('rules' => $object->getId()));
                foreach ( $acls as $acl ) {
                    $acl->removeRuleById($object->getId());
                    $this->getOdm()->persist($acl);
                }
            }

            $this->getOdm()->remove($object);
            $this->getOdm()->flush();

            return true;
        }

        return false;
    }

    public function userRoleRemove($email, $role)
    {
        /** @var User $user  */
        $user = $this->getOdm()->getRepository(self::USER_CLASS)->findOneBy(array('email' => $email));

        if ( ! $user ) {
            return false;
        }

        if ( ! $user->getRole($role) ) {
            return false;
        }

        $user->removeRole($role);

        $this->getOdm()->persist($user);
        $this->getOdm()->flush();

        return true;
    }

    public function listObjects($what, $start, $count)
    {
        $class = $this->getClassFromShortcuts($what);

        /** @var \Doctrine\ODM\MongoDB\Cursor $objects  */
        $objects = $this->getOdm()->getRepository($class)->findAll();

        return $objects->skip($start)->limit($count);
    }
}
