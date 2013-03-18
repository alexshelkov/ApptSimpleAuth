<?php
namespace ApptSimpleAuthStubModuleAuthAllowed\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use ApptSimpleAuth\Feature\AuthControlInterface;

use ApptSimpleAuth\AuthService;
use Zend\Mvc\Router\RouteMatch;

class TestController extends AbstractActionController implements AuthControlInterface
{
    static public $allowed;

    public function isAuthDenied(AuthService $auth, RouteMatch $routeMatch)
    {
        $allowed = self::$allowed;
        return $allowed();
    }

    public function indexAction()
    {
    }
}