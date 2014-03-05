<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\DependencyInjection\ContainerInterface as Container,
    Symfony\Component\Form\Form as Form;
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

    public function createAction(Request $request)
    {
        $form = $this->getCounterForm();
        $form->handleRequest($request);

        if ( ! $form->isValid())
        {
            return $this->redirect($this->generateUrl('dwo_frontpage'));
        }

        $storage = $this->getStorage();
        $userStorage = $this->getUserStorage();
        $counter = $form->getData();

        $owner = $this->isLoggedIn()
            ? $this->getLoggedInUser()
            : $counter->getOwner();

        if ($form->get('public')->isClicked())
        {
            $owner = new UserModel('public');
            $counter->setPublic()->setOwner($owner);
        }
        else
        {
            $counter->setPrivate();

            if ( ! $userStorage->exists($owner->getNick()))
            {
                $this->addNotice('New user saved, welcome ' . $owner->getNick());
                $userStorage->store($owner);
                //$this->get('session')->set('user', $user);
            }
            else if ( ! $this->authenticateUser($owner))
            {
                $this->addError('Wrong Nick and/or password');
                return $this->redirect($this->generateUrl('dwo_frontpage'));
            }
        }

        if ( $storage->exists($counter->getName(), $owner->getId()))
        {
            $this->addNotice('Already existed, showing it');
            return $this->redirectToCounter($counter);
        }
        else
        {
            $storage->store($counter);
            $this->addMessage('Counter created');
            return $this->redirectToCounter($counter);
        }
    }

    public function showAction($name, $owner = 'public')
    {
        $storage = $this->getStorage();
        if ( ! $storage->exists($name, $owner))
        {
            return $this->redirectFromNonExisting($name, $owner);
        }
        $counterModel = $this->getStorage()->load($name, $owner);

        $this->setCounter($counterModel);
        $this->setForm($this->getResetForm(
            $counterModel,
            $this->getLoggedInUser()
        ));
        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:index.html.twig',
            $this->response
        );
    }

    /**
     * @param string $name
     * @param string $owner optional default 'public'
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetAction($name, $owner = 'public')
    {
        $storage = $this->getStorage();

        if ( ! $storage->exists($name, $owner))
        {
            return $this->redirectFromNonExisting($name, $owner);
        }
        $counter = $storage->load($name, $owner);
        $form = $this->getResetForm($counter);
        $user = $this->getUserFromRequest($this->getRequest(), $form);
        if ($counter->isPublic() || $this->authenticateUserForCounter($user, $counter))
        {
            $counter->reset();
        }
        else if ( ! $this->authenticateUserForCounter($user, $counter))
        {
            $this->addError('Wrong Nick and/or password');
            return $this->redirectToCounter($counter);
        }

        $storage->store($counter);

        $this->setCounter($counter);
        $this->setForm($this->getResetForm($counter));

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:index.html.twig',
            $this->response
        );
    }

    /**
     * @param string $name
     * @param string $owner
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function redirectFromNonExisting($name)
    {
        $this->addError('Counter did not exist - would you like to create one?');
        $this->setForm($this->getCounterForm($name));

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Default:index.html.twig',
            $this->response
        );
    }

    /**
     * @param Request $request
     * @param Form $form
     * @return UserModel
     */
    private function getUserFromRequest(Request $request, Form $form)
    {
        $form->handleRequest($request);
        return $form->getData();
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
            : $counter->getOwner()->getNick();

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
}