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
        var_dump($this->get('session')->set('user', $user));
        // set and get session attributes
        $this->addMessage('Welcome ' . $user->getNick());
        return $this->redirect($this->generateUrl('dwo_frontpage'));
    }
}
