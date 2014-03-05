<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\DependencyInjection\ContainerInterface as Container,
    Symfony\Component\Form\Form as Form;
use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Model\UserModel,
    JSomerstone\DaysWithoutBundle\Storage\CounterStorage;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

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
            return $this->getFrontPageRedirection();
        }

        $counter = $form->getData();
        try
        {
            if ($form->get('public')->isClicked())
            {
                $owner = new UserModel('public');
                $counter->setOwner($owner)->setPublic();
            }
            else
            {
                $owner = $this->getAuthenticatedUser($counter);
                $this->setLoggedInUser($owner);
                $counter->setOwner($owner)->setPrivate();
            }

            $this->storeCounterIfNeeded($counter);
        }
        catch (AuthenticationException $e)
        {
            $this->addError($e->getMessage());
            return $this->getFrontPageRedirection();
        }

        return $this->redirectToCounter($counter);
    }

    /**
     * @param CounterModel $counter
     * @return UserModel
     * @throws AuthenticationException if authentication fails
     */
    private function getAuthenticatedUser(CounterModel $counter)
    {
        $owner = $this->isLoggedIn()
            ? $this->getLoggedInUser()
            : $counter->getOwner();

        $userStorage = $this->getUserStorage();

        if ( ! $userStorage->exists($owner->getNick()))
        {
            $userStorage->store($owner);
        }
        elseif ( ! $this->authenticateUser($owner))
        {
            throw new AuthenticationException('Wrong Nick and/or password');
        }
        return $owner;
    }

    private function storeCounterIfNeeded(CounterModel $counter)
    {
        $storage = $this->getStorage();
        if ( $storage->exists($counter->getName(), $counter->getOwner()->getId()))
        {
            $this->addNotice('Already existed, showing it');
        }
        else
        {
            $storage->store($counter);
            $this->addMessage('Counter created');
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
        $this->setForm($this->getCounterForm($name, $this->isLoggedIn()));

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