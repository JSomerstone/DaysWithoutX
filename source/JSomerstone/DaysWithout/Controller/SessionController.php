<?php
namespace JSomerstone\DaysWithout\Controller;

use JSomerstone\DaysWithout\Model\UserModel;
use Symfony\Component\HttpFoundation\Session\Session,
    Symfony\Component\HttpFoundation\Request;
use JSomerstone\DaysWithout\Exception\PublicException;

class SessionController extends BaseController
{
    use SessionTrait;

    public function loginPageAction()
    {
        $this->setForm($this->getLoginForm());

        return $this->render(
            'JSomerstoneDaysWithout:Default:login.html.twig',
            $this->response
        );
    }

    public function signupPageAction()
    {
        return $this->render(
            'JSomerstoneDaysWithout:Default:signup.html.twig',
            $this->response
        );
    }

    public function logoutAction()
    {
        return $this->invokeMethod(
            function ()
            {
                if ($this->isLoggedIn())
                {
                    $this->setLoggedInUser(null);
                    session_destroy();
                    return $this->jsonSuccessResponse('Logged out');
                }
                return $this->jsonErrorResponse('Not logged in');
            }
        );
    }

    /**
     * @param string $nick
     * @param string $password
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginAction($nick, $password)
    {
        return $this->invokeMethod(
            function () use ($nick, $password)
            {
                $this->getInputValidator()->validateField('nick', $nick);
                $userObject = new UserModel($nick, $password);
                if ( ! $this->authenticateUser($userObject))
                {
                    $this->getLogger()->addNotice('Login unsuccessful, nick:'.$nick);
                    return $this->jsonErrorResponse('Wrong Nick and/or password');
                }
                $this->setLoggedInUser($userObject);
                return $this->jsonSuccessResponse("Welcome $nick");
            }
        );
    }
}
