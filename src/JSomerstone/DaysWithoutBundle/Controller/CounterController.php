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
     * @var JSomerstone\DaysWithoutBundle\Storage\UserStorage
     */
    private $userStorage;


    public function createAction(Request $request)
    {
        $form = $this->getCounterForm();
        $form->handleRequest($request);
        $storage = $this->getStorage();

        if ( ! $form->isValid())
        {
            var_dump($form->getErrorsAsString());
            return $this->redirect($this->generateUrl('dwo_frontpage'));
        }
        $counter = $form->getData();
        $owner = $counter->getOwner();

        if ($form->get('public')->isClicked()) {
            $counter->setPublic()
                ->setOwner(new UserModel('public'));
        } else {
            $counter->setPrivate();
        }

        if ( ! $counter->isPublic() && ! $this->authenticateUser($owner)) {
            return $this->redirect($this->generateUrl('dwo_frontpage'));
        }

        if ( $storage->exists($counter->getName(), $counter->getOwner()->getId()))
        {
            $this->addNotice('Already existed, showing it');
            return $this->redirectToCounter($counter);
        } else {
            $storage->store($counter);
            $this->addMessage('Counter created');
            return $this->redirectToCounter($counter);
        }
    }

    public function showAction($name, $owner = 'public')
    {
        $counterModel = $this->getStorage()->load($name, $owner);

        $this->setCounter($counterModel);
        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:index.html.twig',
            $this->response
        );
    }

    public function resetAction($name, $owner = 'public')
    {
        $request = $this->getRequest();

        if ($request->get('reset') != 1)
        {
            return $this->showAction($name);
        }

        $counterModel = $this->getStorage()->load($name, $owner);
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

    private function redirectToCounter(CounterModel $counter)
    {
        $owner = ($counter->isPublic())
            ? 'public'
            : $counter->getOwner()->getId();
        return $this->redirect(
            $this->generateUrl(
                'dwo_show_counter',
                array(
                    'name' => $counter->getName(),
                    'owner' => $owner
                )
            )
        );
    }


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
     * @return JSomerstone\DaysWithoutBundle\Storage\UserStorage|object
     */
    private function getUserStorage()
    {
        if ( ! $this->userStorage) {
            $this->userStorage = $this->get('dayswithout.storage.user');
        }
        return $this->userStorage;
    }

    private function authenticateUser(UserModel $user)
    {
        $userStorage = $this->getUserStorage();
        if ( ! $userStorage->exists($user->getNick())) {
            $this->addMessage('Nick stored');
            return true;
        } elseif ( ! $userStorage->authenticate($user)) {
            $this->addError('Unknown nick or password');
            return false;
        }
        return true;
    }
}