<?php
namespace ApptSimpleAuthStubModuleAuthAllowed\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use ApptSimpleAuth\Feature\AuthControlInterface;

use ApptSimpleAuth\AuthService;
use Zend\Mvc\MvcEvent;

class TestController extends AbstractActionController implements AuthControlInterface
{
    static public $allowed;

    public function isAuthDenied(AuthService $auth, MvcEvent $event)
    {
        $allowed = self::$allowed;
        return $allowed();
    }

    public function indexAction()
    {
    }
}