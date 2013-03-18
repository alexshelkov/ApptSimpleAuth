<?php
namespace ApptSimpleAuth\Zend\View\Helper;

use Zend\View\Helper\HelperInterface;
use Zend\View\Renderer\RendererInterface as Renderer;

use ApptSimpleAuth\Plugin\AbstractAuth;

class Auth extends AbstractAuth implements HelperInterface
{
    /**
     * @var Renderer
     */
    protected $view;

    public function setView(Renderer $view)
    {
        $this->view = $view;
    }

    public function getView()
    {
        return $this->view;
    }
}
