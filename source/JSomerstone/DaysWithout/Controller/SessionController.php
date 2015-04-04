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

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function signupAction(Request $request)
    {
        $nick = $request->get('nick');
        $password = $request->get('password');
        $passwordConfirmation = $request->get('password-confirm');

        try
        {
            $this->validateSignup($nick, $password, $passwordConfirmation);
            $user = $this->signUpUser($nick, $password);
            $this->setLoggedInUser($user);
        }
        catch (PublicException $e)
        {
            $this->addError($e->getMessage());
            return $this->redirect($this->generateUrl('dwo_signup_page'));
        }
        catch (\Exception $e)
        {
            $this->addError('Unexpected exception occurred');
            return $this->redirect($this->generateUrl('dwo_signup_page'));
        }

        $this->addMessage("Welcome $nick, time to create your first counter");
        return $this->getFrontPageRedirection();
    }

    private function validateSignup($nick, $password, $passwordConfirmation)
    {
        $this->getInputValidator()
            ->validateFields(array(
                'nick' => $nick,
                'password' => $password
            ))
            ->validatePassword($password, $passwordConfirmation);
    }

    private function signUpUser($nick, $password)
    {
        $userStorage = $this->getUserStorage();
        if ($userStorage->exists($nick))
        {
            throw new PublicException(
                "Unfortunately nick '$nick' is already taken"
            );
        }
        $user = new UserModel($nick, $password);
        $userStorage->store($user);
        return $user;
    }


    public function logoutAction()
    {
        $user = $this->get('session')->get('user');
        if ($user)
        {
            $this->setLoggedInUser(null);
            $this->addMessage('Logged out');
        }
        return $this->redirect($this->generateUrl('dwo_frontpage'));
    }

    public function loginAction(Request $request)
    {
        $form = $this->getLoginForm();
        $form->handleRequest($request);

        if ( ! $form->isValid())
        {
            return $this->redirect($this->generateUrl('dwo_loginpage'));
        }

        $user = $form->getData();

        if ( ! $this->authenticateUser($user))
        {
            $this->addError('Wrong Nick and/or password');
            return $this->redirect($this->generateUrl('dwo_loginpage'));
        }
        $this->setLoggedInUser($user);
        $this->addMessage('Welcome ' . $user->getNick());

        return $this->getFrontPageRedirection();
    }
}
