<?php
namespace JSomerstone\DaysWithout\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;
use JSomerstone\DaysWithout\Model\CounterModel,
    JSomerstone\DaysWithout\Model\UserModel,
    JSomerstone\DaysWithout\Storage\CounterStorage,
    JSomerstone\DaysWithout\Lib\InputValidatorException;

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

    /**
     * @param $name
     * @param null $owner
     * @return Response
     */
    public function viewCounter($name, $owner = null)
    {
        return $this->invokeMethod(function() use ($name, $owner)
        {
            $counter = $this->loadCounter($name, $owner);
            if ($counter)
            {
                return $this->render(
                    'counter/index.html.twig',
                    array( 'counter' => $counter->toArray())
                );
            }
            else
            {

                return $this->render(
                    'default/404.html.twig',
                    [],
                    Response::HTTP_NOT_FOUND
                );
            }
        });
        $this->applyToResponse(array(
            'name' => $name,
            'owner' => $owner,
            'found' => $this->getCounterStorage()->exists($name, $owner)
        ));

    }

    /**
     * @param $name
     * @param $owner
     * @return bool|CounterModel
     */
    private function loadCounter($name, $owner)
    {
        $storage = $this->getCounterStorage();
        if ( ! $storage->exists($name, $owner))
        {
            return false;
        }
        $counterModel = $storage->load($name, $owner);
        if ( $counterModel->isPrivate() )
        {
            if ( ! $this->isLoggedIn() || $this->getLoggedInUser()->getNick() !== $owner)
            {
                return false;
            }
        }
        return $counterModel;
    }

    /**
     * @param string $name
     * @param string $owner
     * @return JsonResponse
     */
    public function getCounter($name, $owner = null)
    {
        return $this->invokeMethod(function() use ($name, $owner)
        {
            $counter = $this->loadCounter($name, $owner);
            if ($counter)
            {
                return $this->jsonSuccessResponse('',$counter->toArray());

            } else {
                return $this->jsonErrorResponse(
                    'Counter not found',
                    array(),
                    JsonResponse::HTTP_NOT_FOUND
                );
            }
        });
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

    /**
     * @param CounterModel $counter
     */
    private function setCounter(CounterModel $counter)
    {
        $this->applyToResponse(array(
            'data' => $counter->toArray()
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
