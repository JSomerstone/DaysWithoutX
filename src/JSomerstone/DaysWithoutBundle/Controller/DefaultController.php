<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

class DefaultController extends BaseController
{
    /**
     *
     * @var array
     */
    protected $response = array(
        'title' => '<thing>'
    );

    public function indexAction()
    {
        $this->applyToResponse(['sample' => self::randomThing()]);

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Default:index.html.twig',
            $this->response
        );
    }

    private static function randomThing()
    {
        $pool = array(
            'Sweets',
            'Drinking',
            'Sex',
            'Speeding ticket',
            'Good deed',
            'Farting in public',
            'Smoking',
        );
        return $pool[rand(0, count($pool)-1)];
    }
}
