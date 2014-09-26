<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        $this->setForm($this->getCounterForm(null, $this->isLoggedIn()));
        $this->setLatestCounters(array());

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Default:index.html.twig',
            $this->response
        );
    }

    private function setLatestCounters(array $latestCounters)
    {
        $latestCounters = $this->getCounterStorage()->getLatestCounters();
        $this->setToResponse('latest', $latestCounters);
    }
}
