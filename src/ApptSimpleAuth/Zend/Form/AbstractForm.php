<?php
namespace ApptSimpleAuth\Zend\Form;

use Zend\Form\Form;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Model\ViewModel;
use ApptSimpleAuth\Zend\Form\Exception\RuntimeException;

abstract class AbstractForm extends Form
{
    /**
     * @var bool
     */
    protected $isInit = false;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var array|string
     */
    protected $successRedirectParams;

    protected function injectDependencies(array $dependencies, array &$options)
    {
        foreach ($dependencies as $prop => $dependency) {
            if ( ! isset($options[$dependency]) ) {
                continue;
            }
            $prop = is_int($prop) ? $dependency : $prop;
            $this->$prop = $options[$dependency];
            unset($options[$dependency]);
        }
    }

    public function init()
    {
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf'
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
            ),
        ));

        $this->isInit = true;
    }

    public function __construct($name = null, $options = array())
    {
        $dependencies = array('renderer', 'template', 'successRedirectParams' => 'success_redirect_params');
        $this->injectDependencies($dependencies, $options);

        if ( empty($this->renderer) ) {
            throw new RuntimeException('Can\'t create ' . get_called_class() . ' without renderer');
        }

        if ( empty($this->template) ) {
            throw new RuntimeException('Can\'t create ' . get_called_class() . ' without template');
        }

        parent::__construct($name, $options);
    }

    /**
     * @return array|string
     */
    public function getSuccessRedirectParams()
    {
        return $this->successRedirectParams;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return Renderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @return ViewModel
     */
    public function getViewModel()
    {
        $model = new ViewModel(array('form' => $this->prepare()));
        $model->setTemplate($this->getTemplate());

        return $model;
    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->getRenderer()->render($this->getViewModel());
    }
}