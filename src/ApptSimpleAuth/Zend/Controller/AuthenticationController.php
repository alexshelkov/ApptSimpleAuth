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

    protected function redirectByParams($uri)
    {
        if ( ! empty($uri) ) {
            return $this->redirect()->toUrl($uri);
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
            if ( $login->isControllerDisplayEnable() ) {
                return $login->getViewModel();
            } else {
                return $this->createHttpNotFoundModel($response);
            }
        } else {
            $data = $this->params()->fromPost();
            $data['user']['auth_error'] = 0;
            $login->setData($data);
            $isValid = $login->isValid();

            if ( $isValid ) {
                $params = $login->getSuccessRedirectUri();
                $message = "appt['simple_auth']['forms']['login']['success_redirect_params']";
            } else {
                $params = $login->getFailRedirectUri();
                $message = 'fail redirect URI';
            }

            if ( $this->redirectByParams($params) ) {
                $response->setStatusCode(Response::STATUS_CODE_301);
            } else {
                $response->setStatusCode(Response::STATUS_CODE_200);
                $response->setContent('Please setup ' . $message);
            }

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
            return $this->createHttpNotFoundModel($response);
        }

        $logout = $this->getLogout();
        $data = $this->params()->fromPost();
        $logout->setData($data);

        if ( ! $logout->isValid() ) {
            $response->setStatusCode(Response::STATUS_CODE_403);
            return $response;
        }

        $this->getAuth()->deAuthenticate();

        if ( $this->redirectByParams($logout->getSuccessRedirectUri()) ) {
            $response->setStatusCode(Response::STATUS_CODE_301);
        } else {
            $response->setStatusCode(Response::STATUS_CODE_200);
            $response->setContent("Please setup appt['simple_auth']['forms']['logout']['success_redirect_params']");
        }

        return $response;
    }
}