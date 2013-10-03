<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

class DefaultController extends BaseController
{
    /**
     *
     * @var array
     */
    protected $response = array(
        'title' => 'Days Without X - front page'
    );

    public function indexAction()
    {
        $this->applyToResponse(['sample' => 'Smoking']);
        
        return $this->render(
            'JSomerstoneDaysWithoutBundle:Default:index.html.twig',
            $this->response
        );
    }
}
