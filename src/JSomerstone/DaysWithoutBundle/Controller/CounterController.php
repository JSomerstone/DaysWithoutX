<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use JSomerstone\DaysWithoutBundle\Model\CounterModel;

class CounterController extends BaseController
{
    /**
     *
     * @var array
     */
    protected $response = array(
        'title' => '??',
        'count' => 0
    );

    public function createAction(Request $request)
    {
        try
        {
            $counterModel = new \JSomerstone\DaysWithoutBundle\Model\CounterModel(
                $request->get('thing')
            );

            $counterModel->persist('/tmp');

            $this->applyToResponse(array('counter' => $counterModel->toArray()));
        }
        catch (\Exception $e)
        {
            $this->applyToResponse(array(
                'title' => 'Error',
                'message' => $e->getMessage()
            ));
        }

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:index.html.twig',
            $this->response
        );
    }

    public function showAction($name)
    {
        $counterModel = CounterModel::load('/tmp', $name);

        $this->applyToResponse(array('counter' => $counterModel->toArray()));

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:index.html.twig',
            $this->response
        );
    }
}