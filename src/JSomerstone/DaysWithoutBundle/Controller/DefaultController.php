<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        $this->applyToResponse(
            array(
                'loggedIn' =>       $this->isLoggedIn(),
                'latest' =>         $this->getLatestCounters(),
                'resentResets' =>   $this->getResentResets(),
                'succession' =>     $this->getSuccession(),
                'counter_title' =>  $this->getSession()->get('counter-title')
            )
        );
        $this->getSession()->remove('counter-title');
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

    private function getSuccession()
    {
        $pool = array(
            'Smoking',
            'TV',
            'Sweets',
            'Internet',
            'Porn',
            'Drinking',
            'Russia invading a country',
            'USA invading a country',
            'Getting laid',
            'Rain',
            'Seeing the sun',
        );
        return $pool[rand(0, count($pool) -1 )];
    }
}
