<?php
namespace ApptSimpleAuth;

use Zend\Authentication\AuthenticationService;

use SimpleAcl\Role\RoleAggregateInterface;
use SimpleAcl\Resource\ResourceAggregateInterface;

use ApptSimpleAuth\AclService;
use ApptSimpleAuth\Acl;
use SimpleAcl\RuleResultCollection;

class AuthService
{
    /**
     * @var string | bool
     */
    protected $acl = false;

    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var AclService
     */
    protected $aclService;

    /**
     * @var RuleResultCollection
     */
    protected $ruleResultCollection;

    /**
     * @param AclService $aclService
     * @param AuthenticationService $authenticationService
     */
    public function __construct(AclService $aclService, AuthenticationService $authenticationService)
    {
        $this->aclService = $aclService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param string | bool $acl
     *
     * @return AuthService
     */
    public function setAcl($acl)
    {
        $this->acl = $acl;
        return $this;
    }

    /**
     * @return RuleResultCollection
     */
    public function getRuleResultCollection()
    {
        return $this->ruleResultCollection;
    }

    /**
     * @return Acl
     */
    protected function getAcl()
    {
        return $this->getAclService()->getAcl($this->acl);
    }

    /**
     * @return AclService
     */
    protected function getAclService()
    {
        return $this->aclService;
    }

    /**
     * @return AuthenticationService
     */
    protected function getAuthenticationService()
    {
        return $this->authenticationService;
    }

    public function authenticate($email, $password)
    {
        /** @var \DoctrineModule\Authentication\Adapter\ObjectRepository $adapter  */
        $adapter = $this->getAuthenticationService()->getAdapter();

        $adapter->setIdentityValue($email);
        $adapter->setCredentialValue($password);

        $result = $this->getAuthenticationService()->authenticate();

        return $result;
    }

    public function deAuthenticate()
    {
        $this->getAuthenticationService()->clearIdentity();
    }

    /**
     * Checks if access allowed for some resource.
     *
     * Examples:
     *      allowed() returns identity if it exist otherwise returns false
     *
     *      allowed($resource, $rule) check if current identity allowed to
     *                                access for $resource according to $rule
     *
     *      allowed($role, $resource, $rule) check access for $role to
     *                                       $resource according $rule
     *
     *
     * @param $role string | RoleAggregateInterface
     * @param $resource string | ResourceAggregateInterface
     * @param $rule string
     *
     *
     * @return bool|RoleAggregateInterface
     */
    public function allowed()
    {
        $this->ruleResultCollection = null;
        $args = func_get_args();

        if ( count($args) == 0 ) {
            $role = $this->getAuthenticationService()->getIdentity();
            if ( $role ) {
                return $role;
            }
            return false;
        } elseif ( count($args) == 2 ) {
            $role = $this->getAuthenticationService()->getIdentity();
            if ( ! $role ) {
                return false;
            }
            list($resource, $rule) = $args;
        } else {
            list($role, $resource, $rule) = $args;
        }

        if ( ! $acl = $this->getAcl() ) {
            return false;
        }

        $isAllowed = $acl->isAllowedReturnResult($role, $resource, $rule);
        $this->ruleResultCollection = $isAllowed;

        if ( $isAllowed->get() && $role instanceof RoleAggregateInterface ) {
            return $role;
        }

        return $isAllowed->get();
    }
}
