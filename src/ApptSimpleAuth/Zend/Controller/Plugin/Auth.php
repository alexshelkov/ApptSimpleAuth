<?php
namespace ApptSimpleAuth\Zend\Controller\Plugin;

use Zend\Stdlib\DispatchableInterface as Dispatchable;
use Zend\Mvc\Controller\Plugin\PluginInterface;

use ApptSimpleAuth\Plugin\AbstractAuth;

class Auth extends AbstractAuth implements PluginInterface
{
    /**
     * @var Dispatchable
     */
    protected $controller;

    public function setController(Dispatchable $controller)
    {
        $this->controller = $controller;
    }

    public function getController()
    {
        return $this->controller;
    }
}
