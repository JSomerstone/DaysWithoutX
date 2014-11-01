<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\DependencyInjection\ContainerInterface as Container,
    Symfony\Component\Form\Form as Form;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Model\UserModel,
    JSomerstone\DaysWithoutBundle\Storage\CounterStorage;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CounterController extends BaseController
{
    public function createAction(Request $request)
    {
        $headline = $request->get('headline');
        $public =  ! is_null($request->get('public'));
        $protected = ! is_null($request->get('private'));
        $private = ! is_null($request->get('private'));
        $counterStorage = $this->getCounterStorage();


        if ( $this->getInputValidator()->validateField('headline', $headline))
        {
            $this->addWarning('Invalid headline for counter');
            return $this->getFrontPageRedirection();
        }

        $counter = new CounterModel($headline);
        if ( $this->isLoggedIn() )
        {
            $counter->setOwner($this->getLoggedInUser());
        }
        if ($protected && $this->isLoggedIn())
        {
            $counter->setProtected();
        } else if ($private && $this->isLoggedIn())
        {
            $counter->setPrivate();
        }

        if ( $counterStorage->exists($counter->getName(), $counter->getOwnerId()))
        {
            $this->addNotice('Already existed, showing it');
        }
        else
        {
            $counterStorage->store($counter);
            $this->addMessage('Counter created');
        }

        return $this->redirectToCounter($counter);
    }

    public function showAction($name, $owner = null)
    {
        $storage = $this->getCounterStorage();
        if ( ! $storage->exists($name, $owner))
        {
            return $this->redirectFromNonExisting($name, $owner);
        }
        $counterModel = $this->getCounterStorage()->load($name, $owner);
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
     * @param string $owner optional default null
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetAction($name, $owner = null)
    {
        $storage = $this->getCounterStorage();

        if ( ! $storage->exists($name, $owner))
        {
            return $this->redirectFromNonExisting($name, $owner);
        }
        $counter = $storage->load($name, $owner);
        $user = $this->getReseter($this->getRequest(), $this->getResetForm($counter));
        if ($counter->isPublic())
        {
            $counter->reset();
        }
        else if ( $this->authenticateUserForCounter($user, $counter))
        {
            $counter->reset();
            $this->setLoggedInUser($user);
        }
        else
        {
            $this->addError('Wrong Nick and/or password');
            return $this->redirectToCounter($counter);
        }

        $storage->store($counter);

        return $this->redirectToCounter($counter);
    }

    public function showUsersCountersAction($user)
    {
        $counterStorage = $this->getCounterStorage();
        $userStorage  = $this->getUserStorage();
        $userObject = $userStorage->load($user);
        if ( ! $userObject)
        {
            $this->addError('User not found');
            return $this->getFrontPageRedirection();
        }
        $this->setToResponse('owner', $userObject);
        $this->setToResponse(
            'counters',
            $this->counterStorage->getUsersCounters($userObject->getNick())
        );

        return $this->render(
            'JSomerstoneDaysWithoutBundle:Counter:usersCounters.html.twig',
            $this->response
        );
    }

    /**
     * @param string $name
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
    private function getReseter(Request $request, Form $form)
    {
        if ($this->isLoggedIn())
            return $this->getLoggedInUser();

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
        $owner = ($counter->getOwner() === null)
            ? null
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
}
