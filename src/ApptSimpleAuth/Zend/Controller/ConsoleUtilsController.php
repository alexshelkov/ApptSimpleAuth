<?php
namespace ApptSimpleAuth\Zend\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use ApptSimpleAuth\ManagerService;
use ApptSimpleAuth\AuthService;
use ApptSimpleAuth\User;

class ConsoleUtilsController extends AbstractActionController
{
    /**
     * @return ManagerService
     */
    public function getManager()
    {
        return $this->getServiceLocator()->get('appt.simple_auth.manager');
    }

    /**
     * @return AuthService
     */
    public function getAuth()
    {
        return $this->getServiceLocator()->get('appt.simple_auth.auth');
    }

    protected function castStringToBool($string)
    {
        $string = strtolower($string);
        if ( $string === 'false' ) {
            $string = false;
        } elseif ( $string === 'true' ) {
            $string = true;
        }

        return (bool)$string;
    }

    protected function dbNamesToString($dbNames)
    {
        $dbNamesString = '';
        foreach ( $dbNames as $dbName ) {
            $dbNamesString .= '\'' . $dbName . '\', ';
        }

        return 'Databases was cleared: ' . rtrim($dbNamesString, ', ') . "\n";
    }

    public function aclAddAction()
    {
        $name = $this->getRequest()->getParam('name', false);

        $acl = $this->getManager()->addAcl($name);

        $name = $acl->getName();

        return "Acl '$name' was created\n";
    }

    public function userAddAction()
    {
        $email = strtolower($this->getRequest()->getParam('email'));
        $pass = $this->getRequest()->getParam('password', null);
        $roleName = $this->getRequest()->getParam('role', false);

        $this->getManager()->addUser($email, $pass, $roleName);

        return "User '$email' with password '$pass' was created\n";
    }

    public function roleOrResourceAddAction()
    {
        $what = $this->getRequest()->getParam('what');
        $name = $this->getRequest()->getParam('name');
        $childName = $this->getRequest()->getParam('child', false);

        if ( $what == 'role' ) {
            $this->getManager()->addRole($name, $childName);
        } else {
            $this->getManager()->addResource($name, $childName);
        }

        $what = ucfirst($what);

        return "$what '$name' was created" . ($childName ? " with child '$childName'" : '') . "\n";
    }

    public function ruleAddAction()
    {
        $roleName = $this->getRequest()->getParam('role');
        $resourceName = $this->getRequest()->getParam('resource');
        $ruleName = $this->getRequest()->getParam('rule');
        $allow = (bool)$this->castStringToBool($this->getRequest()->getParam('allow', true));
        $acl = $this->getRequest()->getParam('acl');

        $acl = $this->getManager()->addRule($ruleName, $roleName, $resourceName, $allow, $acl);

        $aclName = $acl->getName();

        return ($allow ? 'Allow' : 'Deny') .
            " rule '$ruleName' was created for role '$roleName' and resource '$resourceName' in '$aclName' acl\n";
    }

    public function removeAction()
    {
        $what = $this->getRequest()->getParam('what');
        $name = $this->getRequest()->getParam('name');
        $useRuleName = $this->castStringToBool($this->getRequest()->getParam('use-rule-name', true));

        $isRemoved = $this->getManager()->remove($what, $name, $useRuleName);

        $what = ucfirst($what);

        if ( $isRemoved ) {
            return "$what '$name' was removed\n";
        } else {
            return "$what '$name' was not found\n";
        }
    }

    public function listAction()
    {
        $what = $this->getRequest()->getParam('what');
        $start = $this->getRequest()->getParam('start', 0);
        $count = $this->getRequest()->getParam('count', 200);

        $objects = $this->getManager()->listObjects($what, $start, $count);

        $total = count($objects);

        $list = '';
        $count = 0;
        /** @var \SimpleAcl\Role | \SimpleAcl\Resource | \ApptSimpleAuth\User | \ApptSimpleAuth\Rule $object */
        foreach ( $objects as $object ) {
            $list .= "\t";
            if ( $what == 'acl' ) {
                $list .= $object->getName();
            } elseif ( $what == 'user' ) {
                $list .= $object->getEmail() . " ";

                $roleString = '';
                foreach ($object->getRolesNames() as $role) {
                    $roleString .= $role . ', ';
                }
                $roleString = rtrim($roleString, ', ');

                $list .= "(" . $roleString . ")";
            } elseif ( $what == 'role' || $what == 'resource' ) {
                $list .= $object->getName();

                $children = $object->getChildren();
                if ( count($children) ) {
                    $list .= ' -> ';
                    foreach ( $children as $child ) {
                        $list .= $child->getName() . ' ';
                    }
                    $list = rtrim($list);

                }
            } elseif ( $what == 'rule' ) {
                $list .= $object->getId() . " " . $object->getName() . " ";

                if ( $role = $object->getRole() ) {
                    $list .= $role->getName() . " ";
                }
                if ( $resource = $object->getResource() ) {
                    $list .= $resource->getName() . " ";
                }

                $list .= $object->getAction() ? 'true' : 'false';
            }

            $list .= "\n";

            $count++;
        }

        $list = "Showing $count (of $total) {$what}s from $start\n" . $list;

        return $list;
    }

    public function userRoleRemoveAction()
    {
        $email = $this->getRequest()->getParam('email');
        $role = $this->getRequest()->getParam('role');

        $isRemoved = $this->getManager()->userRoleRemove($email, $role);

        if ( $isRemoved ) {
            return "Role '$role' was removed from '$email'\n";
        } else {
            return "Role or user not found\n";
        }
    }

    public function allowedAction()
    {
        $isDetails = $this->getRequest()->getParam('isDetails');
        $aclName = $this->getRequest()->getParam('acl', false);
        $roleName = $this->getRequest()->getParam('role');
        $resourceName = $this->getRequest()->getParam('resource');
        $ruleName = $this->getRequest()->getParam('rule');

        $auth = $this->getAuth();
        $auth->setAcl($aclName);

        $params = array();
        foreach ( array($roleName, $resourceName, $ruleName) as $param ) {
            if ( $param ) {
                $params[] = $param;
            }
        }

        $isAllowed = call_user_func_array(array($auth, 'allowed'), $params);

        $message = ($isAllowed ? 'yes' : 'no') . "\n";

        if ( $isDetails ) {
            $ruleResultCollection = $auth->getRuleResultCollection() ? $auth->getRuleResultCollection() : array();

            $message .= "\nRules applied (first wins):\n";
            /** @var \SimpleAcl\RuleResult $ruleResult */
            foreach ( $ruleResultCollection as $ruleResult ) {
                $allow = $ruleResult->getAction() ? 'allow' : 'deny';
                $message .= "\t" . $ruleResult->getRule()->getId() . ' ' . $allow . ' '. $ruleResult->getPriority() . "\n";
            }

            if ( ! $ruleResultCollection->any() ) {
                $message .= "\tnone\n";
            }
        }

        return $message;
    }

    public function clearAction()
    {
        $dbName = $this->getManager()->clearDb();
        return $this->dbNamesToString($dbName);
    }
}
