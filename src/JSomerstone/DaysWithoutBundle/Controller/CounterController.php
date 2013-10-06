<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\DependencyInjection\ContainerInterface as Container;
use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Model\UserModel,
    JSomerstone\DaysWithoutBundle\Storage\CounterStorage;

class CounterController extends BaseController
{
    private $counterLocation = '/tmp/dayswithout-behat';

    /**
     *
     * @var JSomerstone\DaysWithoutBundle\Storage\CounterStorage
     */
    private $counterStorage;


    /**
     *
     * @return JSomerstone\DaysWithoutBundle\Storage\CounterStorage
     */
    private function getStorage()
    {
        if ( ! isset($this->counterStorage)) {
            $this->counterStorage = $this->get('dayswithout.storage.counter');
        }
        return $this->counterStorage;
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
            $user = null;
            $storage = $this->getStorage();
            if ( $storage->exists($thing, $user))
            {
                $this->applyToResponse(array('notice' => 'Already existed'));
                $counterModel = $storage->load($thing);
            } else {
                $counterModel = new CounterModel($thing, date('Y-m-d'), $user);
                $storage->store($counterModel);

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
        }

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:index.html.twig',
            $this->response
        );
    }

    public function showAction($name)
    {
        $counterModel = $this->getStorage()->load($name);

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

        $counterModel = $this->getStorage()->load($name);
        $counterModel->reset();
        $this->getStorage()->store($counterModel);

        $this->applyToResponse(array(
            'counter' => $counterModel
        ));

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:index.html.twig',
            $this->response
        );
    }
}