<?php
namespace JSomerstone\DaysWithout\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\DependencyInjection\ContainerInterface as Container,
    Symfony\Component\Form\Form as Form;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JSomerstone\DaysWithout\Model\CounterModel,
    JSomerstone\DaysWithout\Model\UserModel,
    JSomerstone\DaysWithout\Storage\CounterStorage,
    JSomerstone\DaysWithout\Lib\InputValidatorException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CounterController extends BaseController
{
    use SessionTrait;

    public function createAction(Request $request)
    {
        $headline = $request->get('headline');
        $public =  ! is_null($request->get('public'));
        $protected = ! is_null($request->get('protected'));
        $private = ! is_null($request->get('private'));
        $counterStorage = $this->getCounterStorage();

        try
        {
            $this->getInputValidator()->validateField('headline', $headline);
        }
        catch ( InputValidatorException $e )
        {
            $this->addError("Headline for counter was invalid");
            return $this->getFrontPageRedirection();
        }

        $counter = new CounterModel($headline);
        if ( $this->isLoggedIn() )
        {
            $counter->setOwner($this->getLoggedInUser());

            if ($protected)
            {
                $counter->setProtected();
            } else if ($private)
            {
                $counter->setPrivate();
            }
        }

        if ($public)
        {
            $counter->setPublic();
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
        $counterModel = $storage->load($name, $owner);
        if ( $counterModel->isPrivate() )
        {
            if ( ! $this->isLoggedIn() || $this->getLoggedInUser()->getNick() !== $owner)
            {
                return $this->redirectFromNonExisting($name, $owner);
            }
        }

        $this->setCounter($counterModel);

        return $this->render(
            'JSomerstoneDaysWithout:Counter:index.html.twig',
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
        else if ( $this->authoriseUserForCounter($user, $counter))
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
        $userStorage  = $this->getUserStorage();
        $owner = $userStorage->load($user);
        if ( ! $owner)
        {
            $this->addError('User not found');
            return $this->getFrontPageRedirection();
        }

        $showPrivateCounters = ($this->isLoggedIn() && $owner->isSameAs($this->getLoggedInUser()));

        $this->applyToResponse([
            'owner' => $owner,
            'counters' => $this->getCounterStorage()->getUsersCounters(
                $owner->getNick(),
                $showPrivateCounters
            )
        ]);

        return $this->render(
            'JSomerstoneDaysWithout:Counter:usersCounters.html.twig',
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
        $this->getSession()->set('counter-title', ucfirst(str_replace('-', ' ', $name)));

        return $this->redirect($this->generateUrl('dwo_frontpage'));
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

    /**
     * @param string $name
     * @param string|null $owner
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($name, $owner = null)
    {
        $counter = $this->getCounterStorage()->load($name, $owner);

        if ( ! $counter)
        {
            return $this->jsonResponse( false, "Counter not found" );
        }
        else if ( ! $this->isLoggedIn() || ! $counter->isOwnedBy($this->getLoggedInUser()))
        {
            return $this->jsonResponse( false, "Unauthorized action" );
        }

        $this->getCounterStorage()->remove($counter);
        $this->addNotice('Counter Removed');

        $redirUrl = isset($owner)
            ? $this->generateUrl('dwo_list_user_counters', array('user' => $owner))
            : $this->generateUrl('dwo_frontpage');

        return $this->jsonResponse(
            true,
            "Counter removed",
            $redirUrl
        );
    }
}