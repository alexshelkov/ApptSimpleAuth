<?php
namespace ApptSimpleAuth\Feature;

use ApptSimpleAuth\AuthService;
use Zend\Mvc\Router\RouteMatch;

interface AuthControlInterface
{
    /**
     * @param AuthService $auth
     * @param RouteMatch $routeMatch
     *
     * @return bool | string
     */
    public function isAuthDenied(AuthService $auth, RouteMatch $routeMatch);
}