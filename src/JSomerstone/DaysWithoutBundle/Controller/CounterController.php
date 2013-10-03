<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use JSomerstone\DaysWithoutBundle\Model\CounterModel;

class CounterController extends BaseController
{
    private $counterLocation = '/tmp';
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
            $thing = $request->get('thing');
            if ( CounterModel::exists($this->counterLocation, $thing))
            {
                $counterModel = CounterModel::load($this->counterLocation, $thing);
                $this->applyToResponse(array('notice' => 'Already existed'));
            } else {
                $counterModel = new CounterModel($thing);
                $counterModel->persist($this->counterLocation);

                $this->applyToResponse(array(
                    'message' => 'Counter created',
                ));
            }


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
        $counterModel = CounterModel::load($this->counterLocation, $name);

        $this->applyToResponse(array(
            'counter' => $counterModel->toArray()
        ));

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:index.html.twig',
            $this->response
        );
    }
}