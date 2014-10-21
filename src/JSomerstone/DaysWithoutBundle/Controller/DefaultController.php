<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        $this->setToResponse('loggedIn', $this->isLoggedIn());
        $this->setToResponse('latest', $this->getLatestCounters());
        $this->setToResponse('resentResets', $this->getResentResets());

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Default:index.html.twig',
            $this->response
        );
    }

    private function getLatestCounters()
    {
        return $this->getCounterStorage()->getLatestCounters();
    }

    private function getResentResets()
    {
        return $this->getCounterStorage()->getResentResetsCounters();
    }
}
