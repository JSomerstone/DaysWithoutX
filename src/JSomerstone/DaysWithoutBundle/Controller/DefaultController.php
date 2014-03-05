<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        $this->setForm($this->getCounterForm(null, $this->isLoggedIn()));

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Default:index.html.twig',
            $this->response
        );
    }
}
