<?php
namespace ApptSimpleAuth\Feature;

use ApptSimpleAuth\AuthService;
use Zend\Mvc\MvcEvent;

interface AuthControlInterface
{
    /**
     * @param AuthService $auth
     * @param MvcEvent $event
     *
     * @return bool | string
     */
    public function isAuthDenied(AuthService $auth, MvcEvent $event);
}