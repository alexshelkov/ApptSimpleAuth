<?php
namespace ApptSimpleAuth\Zend\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use Zend\Http\Response;

use ApptSimpleAuth\AuthService;
use ApptSimpleAuth\Zend\Form\Logout;
use ApptSimpleAuth\Zend\Form\Login;

class AuthenticationController extends AbstractActionController
{
    /**
     * @return AuthService
     */
    protected function getAuth()
    {
        return $this->getServiceLocator()->get('appt.simple_auth.auth');
    }

    /**
     * @return Login
     */
    protected function getLogin()
    {
        return $this->getServiceLocator()->get('FormElementManager')->get('appt.simple_auth.form.login');
    }

    /**
     * @return Logout
     */
    protected function getLogout()
    {
        return $this->getServiceLocator()->get('FormElementManager')->get('appt.simple_auth.form.logout');
    }

    protected function redirectByParams($params)
    {
        if ( is_array($params) ) {
            return $this->redirect()->toRoute($params['route'], $params['params'], $params['options'], $params['reuse_matched_params']);
        } elseif ( is_string($params) ) {
            return $this->redirect()->toUrl($params);
        }

        return false;
    }

    public function loginAction()
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $login = $this->getLogin();

        // if not post just render form
        if ( ! $request->isPost() ) {
            return $login->getViewModel();
        } else {

            $login->setData($this->params()->fromPost());

            $isValid = $login->isValid();

            if ( $isValid ) {
                $params = $login->getSuccessRedirectParams();
                $message = "appt['simple_auth']['forms']['login']['success_redirect_params']";
            } else {
                $params = $login->getFailRedirectUri();
                $message = 'fail redirect URI';
            }

            if ( $this->redirectByParams($params) ) {
                $response->setStatusCode(Response::STATUS_CODE_301);
            } else {
                $response->setStatusCode(Response::STATUS_CODE_200);
            }
            $response->setContent('Please setup ' . $message);

        }

        return $response;
    }

    public function logoutAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        /** @var Response $response */
        $response = $this->getResponse();

        // not do anything if not post request
        if ( ! $request->isPost() ) {
            $response->setStatusCode(Response::STATUS_CODE_404);
            return $response;
        }

        $logout = $this->getLogout();
        $data = $this->params()->fromPost();
        $logout->setData($data);

        if ( ! $logout->isValid() ) {
            $response->setStatusCode(Response::STATUS_CODE_403);
            return $response;
        }

        $this->getAuth()->deAuthenticate();

        if ( $this->redirectByParams($logout->getSuccessRedirectParams()) ) {
            $response->setStatusCode(Response::STATUS_CODE_301);
        } else {
            $response->setStatusCode(Response::STATUS_CODE_200);
            $response->setContent("Please setup appt['simple_auth']['forms']['logout']['success_redirect_params']");
        }

        return $response;
    }
}