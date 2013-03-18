<?php
namespace ApptSimpleAuth;

use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

use Zend\Http\Response;
use Zend\Http\Request;
use Zend\Stdlib\CallbackHandler;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

use ApptSimpleAuth\Feature\AuthControlInterface;
use ApptSimpleAuth\Exception\AccessViolationException;

class EventListener implements SharedListenerAggregateInterface, ListenerAggregateInterface
{
    protected $eventIdentifier = 'Zend\Stdlib\DispatchableInterface';

    /**
     * @var string
     */
    protected $errorTemplate;

    /**
     * @var CallbackHandler[]
     */
    protected $listeners;

    /**
     * @var CallbackHandler[]
     */
    protected $sharedListeners = array();

    /**
     * @param string $errorTemplate
     */
    public function setErrorTemplate($errorTemplate)
    {
        $this->errorTemplate = $errorTemplate;
    }

    /**
     * @return string
     */
    public function getErrorTemplate()
    {
        return $this->errorTemplate;
    }

    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), -91);
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ( $events->detach($listener) ) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function attachShared(SharedEventManagerInterface $events)
    {
        $this->sharedListeners[] = $events->attach($this->eventIdentifier, MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'), 2);
    }

    public function detachShared(SharedEventManagerInterface $events)
    {
        foreach ($this->sharedListeners as $index => $listener) {
            if ( $events->detach($this->eventIdentifier, $listener) ) {
                unset($this->sharedListeners[$index]);
            }
        }
    }

    /**
     * @param MvcEvent $event
     *
     * @return bool | object
     */
    protected function getController(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();

        if ( !$routeMatch ) {
            return false;
        }

        if ( ! $controllerName = $routeMatch->getParam('controller', false) ) {
            return false;
        }

        $application = $event->getApplication();
        $controllerLoader = $application->getServiceManager()->get('ControllerLoader');

        if ( ! $controllerLoader->has($controllerName) ) {
            return false;
        }

        return $controllerLoader->get($controllerName);
    }

    /**
     * @param MvcEvent $event
     *
     * @return bool|object
     */
    public function getModule(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();

        if ( ! $routeMatch ) {
            return false;
        }

        if ( ! $controllerName = $routeMatch->getParam('controller', false) ) {
            return false;
        }

        $moduleName = substr($controllerName, 0, strpos($controllerName, '\\'));

        /** @var \Zend\ModuleManager\ModuleManager $moduleManager  */
        $moduleManager = $event->getApplication()->getServiceManager()->get('ModuleManager');

        if ( ! $module = $moduleManager->getModule($moduleName) ) {
            return false;
        }

        return $module;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    protected function isAuthControlled($object)
    {
        if ( is_object($object) ) {
            return $object instanceof AuthControlInterface;
        }

        return false;
    }

    /**
     * @param AuthControlInterface $object
     * @param MvcEvent $event
     *
     * @throws AccessViolationException
     */
    protected function callAuthControl(AuthControlInterface $object, MvcEvent $event)
    {
        $auth = $event->getApplication()->getServiceManager()->get('appt.simple_auth.auth');

        try {
            $isDenied = $object->isAuthDenied($auth, $event->getRouteMatch());
        } catch (AccessViolationException $isDenied) {}

        if ( $isDenied ) {
            if ( ! $isDenied instanceof AccessViolationException ) {
                if ( ! is_string($isDenied) ) {
                    $message = 'Access not allowed';
                } else {
                    $message = $isDenied;
                }

                $isDenied = new AccessViolationException($message);
            }

            if ( ! $isDenied->getUri() && ($request = $event->getRequest()) instanceof Request ) {
                $isDenied->setUri($request->getUri());
            }

            if ( ($response = $event->getResponse()) instanceof Response ) {
                $response->setStatusCode($isDenied->getStatusCode());
            }

            throw $isDenied;
        }
    }

    /**
     * @param MvcEvent $event
     */
    public function onDispatch(MvcEvent $event)
    {
        $authControlled = array($this->getModule($event), $this->getController($event));

        foreach ( $authControlled as $object ) {
            if ( $this->isAuthControlled($object) ) {
                $this->callAuthControl($object, $event);
            }
        }
    }

    /**
     * @param MvcEvent $event
     */
    public function onDispatchError(MvcEvent $event)
    {
        if ( $event->getError() == Application::ERROR_EXCEPTION &&
            $event->getParam('exception') instanceof AccessViolationException &&
            ($template = $this->getErrorTemplate()) &&
            ($model = $event->getResult()) instanceof ViewModel )  {

            $model->setTemplate($template);
        }
    }
}
