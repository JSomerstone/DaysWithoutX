<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use JSomerstone\DaysWithoutBundle\Model\CounterModel;
use JSomerstone\DaysWithoutBundle\Storage\CounterStorage;

class CounterController extends BaseController
{
    private $counterLocation = '/tmp/dayswithout-behat';
    private $counterStorage;

    public function __construct()
    {
        $this->counterStorage = new CounterStorage($this->counterLocation);
    }

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
            if ( $this->counterStorage->exists($thing))
            {
                $this->applyToResponse(array('notice' => 'Already existed'));
                $counterModel = $this->counterStorage->load($thing);
            } else {
                $counterModel = new CounterModel($thing);
                $this->counterStorage->store($counterModel);

                $this->applyToResponse(array(
                    'message' => 'Counter created',
                ));
            }
            $this->applyToResponse(array('counter' => $counterModel));
        }
        catch (\Exception $e)
        {
            $this->applyToResponse(array(
                'title' => 'Error',
                'message' => $e->getMessage()
            ));
            return $this->redirect('/');
        }

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:index.html.twig',
            $this->response
        );
    }

    public function showAction($name)
    {
        $counterModel = $this->counterStorage->load($name);

        $this->applyToResponse(array(
            'counter' => $counterModel
        ));

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:index.html.twig',
            $this->response
        );
    }

    public function resetAction($name)
    {
        $request = $this->getRequest();

        if ($request->get('reset') != 1)
        {
            return $this->showAction($name);
        }

        $counterModel = $this->counterStorage->load($name);
        $counterModel->reset();
        $this->counterStorage->store($counterModel);

        $this->applyToResponse(array(
            'counter' => $counterModel
        ));

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:index.html.twig',
            $this->response
        );
    }
}