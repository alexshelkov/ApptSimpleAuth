<?php
namespace ApptSimpleAuth\Zend\Form;

use Zend\Form\Form;
use Zend\Validator\Csrf as CsrfValidator;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Model\ViewModel;
use ApptSimpleAuth\Zend\Form\Exception\RuntimeException;
use ApptSimpleAuth\Zend\Form\Exception\DomainException;
use Zend\Mvc\Router\RouteInterface;

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
     * @var RouteInterface
     */
    protected $router;

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

    /**
     * @return RouteInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    public function init()
    {
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf',
        ));

	    // each validator must have unique name
	    // otherwise they may be in conflict
	    $this->get('csrf')->setCsrfValidator(new CsrfValidator(array(
		    'timeout' => 86400,
		    'name'    => 'ApptSimpleAuth_' . $this->getName() . '_csrf'
	    )));

        $this->add(array(
            'name' => 'success_uri',
            'type' => 'text',
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
            ),
        ));

        $this->get('success_uri')->setValue($this->getSuccessRedirectUriByParams());

        $this->isInit = true;
    }

    public function __construct($name = null, $options = array())
    {
        $dependencies = array('renderer', 'template', 'router', 'successRedirectParams' => 'success_redirect_params');
        $this->injectDependencies($dependencies, $options);

        if ( ! $this->renderer instanceof Renderer ) {
            throw new RuntimeException('Can\'t create ' . get_called_class() . ' without renderer');
        }

        if ( empty($this->template) ) {
            throw new RuntimeException('Can\'t create ' . get_called_class() . ' without template');
        }

        if ( ! $this->router instanceof RouteInterface  ) {
            throw new RuntimeException('Can\'t create ' . get_called_class() . ' without router');
        }

        parent::__construct($name, $options);
    }

    /**
     * @return array|string
     */
    protected function getSuccessRedirectParams()
    {
        return $this->successRedirectParams;
    }

    protected function getUriByParams($params)
    {
        if ( is_array($params) ) {
            $options = $params['options'];
            $options['name'] = $params['route'];
            return (string)$this->getRouter()->assemble($params['params'], $options);
        } else {
            return (string)$params;
        }
    }

    protected function getSuccessRedirectUriByParams()
    {
        return $this->getUriByParams($this->getSuccessRedirectParams());
    }

    public function getSuccessRedirectUri()
    {
        if ( !$this->hasValidated ) {
            throw new DomainException(
                __METHOD__ . ' cannot get redirect uri as validation has not yet occurred'
            );
        }

        $data = $this->getData();

        if ( ! isset($data['success_uri']) ) {
            throw new DomainException(
                __METHOD__ . ' cannot get redirect uri as no uri in data'
            );
        }

        $uri = $data['success_uri'];

        return $uri;
    }

    public function setSuccessRedirectUri($successRedirectUri)
    {
        if ( !$this->isInit ) {
            throw new DomainException(
                __METHOD__ . ' cannot set redirect uri as form not init yet'
            );
        }

        $this->get('success_uri')->setValue($successRedirectUri);
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