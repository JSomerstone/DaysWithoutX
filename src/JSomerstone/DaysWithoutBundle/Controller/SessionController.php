<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session,
    Symfony\Component\HttpFoundation\Request;

class SessionController extends BaseController
{
    //use SessionTrait;

    public function loginPageAction()
    {
        $this->setForm($this->getLoginForm());

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Default:login.html.twig',
            $this->response
        );
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

        if ( ! $this->authenticateUser($user, $this->getUserStorage()))
        {
            $this->addError('Wrong Nick and/or password');
            return $this->redirect($this->generateUrl('dwo_loginpage'));
        }
        $this->setLoggedInUser($user);
        $this->addMessage('Welcome ' . $user->getNick());

        return $this->getFrontPageRedirection();
    }
}
