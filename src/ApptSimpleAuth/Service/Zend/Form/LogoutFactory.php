<?php
namespace ApptSimpleAuth\Service\Zend\Form;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\FormElementManager;

use ApptSimpleAuth\Service\Exception\InvalidArgumentException;

use ApptSimpleAuth\Zend\Form\AbstractForm;
use ApptSimpleAuth\Zend\Form\Logout;
use ApptSimpleAuth\Service\Options\Forms as Options;
use Zend\Mvc\Router\RouteStackInterface;

class LogoutFactory implements FactoryInterface
{
    protected $formClass = 'ApptSimpleAuth\Zend\Form\Logout';

    protected function getFormName()
    {
        return substr($this->formClass, strrpos($this->formClass, '\\') + 1);
    }

    protected function addDependencies(Options $serviceOptions, array $options, ServiceLocatorInterface $serviceLocator)
    {
        $options['auth'] = $serviceLocator->get('appt.simple_auth.auth');
        $options['renderer'] = $serviceLocator->get($serviceOptions->getRenderer());
        $options['router'] = $serviceLocator->get('router');

        $template = "get{$this->getFormName()}Template";
        $options['template'] = $serviceOptions->$template();

        $success = "get{$this->getFormName()}SuccessRedirectParams";
        $options['success_redirect_params'] = $serviceOptions->$success();

        return $options;
    }

    protected function setFormAction(AbstractForm $form, ServiceLocatorInterface $serviceLocator)
    {
        /** @var RouteStackInterface $router */
        $router = $serviceLocator->get('router');
        $name = 'aauth/' . strtolower($this->getFormName());
        $uri = $router->assemble(array(), array('name' => $name));
        $form->setAttribute('action', $uri);
    }

    /**
     * @param ServiceLocatorInterface $formElementManager
     *
     * @return Logout
     *
     * @throws InvalidArgumentException
     */
    public function createService(ServiceLocatorInterface $formElementManager)
    {
        if ( ! $formElementManager instanceof FormElementManager ) {
            throw new InvalidArgumentException('Except instance of Zend\Form\FormElementManager got ' . get_class($formElementManager));
        }

        $serviceLocator = $formElementManager->getServiceLocator();

        $serviceOptions = Options::init($serviceLocator);

        $options = array();
        $options = $this->addDependencies($serviceOptions, $options, $serviceLocator);

        $formClass = $this->formClass;
        $form = new $formClass(null, $options);
        $this->setFormAction($form, $serviceLocator);

        return $form;
    }
}