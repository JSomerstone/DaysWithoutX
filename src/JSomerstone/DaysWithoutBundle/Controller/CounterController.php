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

    public function createAction(Request $request)
    {
        $form = $this->getCounterForm();
        $form->handleRequest($request);

        if ( ! $form->isValid())
        {
            return $this->redirect($this->generateUrl('dwo_frontpage'));
        }
        $counter = $form->getData();
        if ($form->get('public')->isClicked())
        {
            $counter->setOwner(new UserModel('public'));
        }
        $storage = $this->getStorage();
        if ( $storage->exists($counter->getName(), $counter->getOwner()->getNick()))
        {
            $this->addNotice('Already existed, showing it');
            return $this->redirectToCounter($counter->getName());
        } else {
            $storage->store($counter);
            $this->addMessage('Counter created');
            return $this->redirectToCounter($counter->getName());
        }
    }

    public function showAction($name)
    {
        $counterModel = $this->getStorage()->load($name);

        $this->setCounter($counterModel);

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

    private function setCounter($counter)
    {
        $this->applyToResponse(array(
            'counter' => $counter
        ));
    }

    private function redirectToCounter($name)
    {
        return $this->redirect(
            $this->generateUrl(
                'dwo_show_counter',
                array('name' => $name)
            )
        );
    }
}