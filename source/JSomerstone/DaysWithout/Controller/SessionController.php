<?php
namespace JSomerstone\DaysWithout\Controller;

use JSomerstone\DaysWithout\Model\UserModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session,
    Symfony\Component\HttpFoundation\Request;
use JSomerstone\DaysWithout\Exception\PublicException;

class SessionController extends BaseController
{
    use SessionTrait;

    public function logoutAction()
    {
        return $this->invokeMethod(function(){
            $this->logoutUser();
            return $this->jsonSuccessResponse('Logged out');
        });
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
                $userObject = new UserModel($nick, null, $password);
                if ( ! $this->authenticateUser($userObject))
                {
                    $this->getLogger()->addNotice('Login unsuccessful, nick:'.$nick);
                    return $this->jsonWarningResponse(
                        'Wrong Nick and/or password',
                        JsonResponse::HTTP_FORBIDDEN
                    );
                }
                $this->getSession()->set('user', $userObject);
                session_regenerate_id(true);
                return $this->jsonSuccessResponse("Welcome $nick");
            }
        );
    }
}
