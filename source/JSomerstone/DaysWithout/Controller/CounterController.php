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

    /**
     * @param string $headline
     * @param string $visibility
     * @return JsonResponse
     */
    public function createAction($headline, $visibility)
    {
        return $this->invokeMethod(function() use ($headline, $visibility)
        {
            $this->getInputValidator()->validateFields([
                'headline' => $headline,
                'visibility' => $visibility
            ]);

            $counterStorage = $this->getCounterStorage();
            $counter = new CounterModel($headline);

            if ( $this->isLoggedIn() )
            {
                $counter->setOwner($this->getLoggedInUser())
                    ->setVisibility($visibility);
            } else {
                $counter->setPublic();
            }

            if ( $counterStorage->exists($counter->getName(), $counter->getOwnerId()))
            {
                return $this->jsonSuccessResponse(
                    'Already existed, showing it',
                    $counter->toArray(),
                    JsonResponse::HTTP_MOVED_PERMANENTLY
                );
            }
            else
            {
                $counterStorage->store($counter);
                $this->getLogger()->addInfo("counter created", $counter->toArray());
                return $this->jsonSuccessResponse(
                    'Counter created',
                    $counter->toArray(),
                    JsonResponse::HTTP_CREATED
                );
            }
        });
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
                    array( 'counter' => $counter )
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
        if ( $counterModel->isPrivate() && ! $counterModel->isOwnedBy($this->getLoggedInUser()))
        {
            return false;
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
                $this->getLogger()->addNotice("Counter not found, name:$name, owner:$owner");
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
     * @param string $comment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetAction($name, $owner = null, $comment = null)
    {
        return $this->invokeMethod(function() use ($name, $owner, $comment)
        {
            $this->getInputValidator()->validateFields([
                'name' => $name,
                'comment' => $comment
            ]);
            $storage = $this->getCounterStorage();
            if ( ! $storage->exists($name, $owner))
            {
                return $this->jsonErrorResponse('Counter not found');
            }
            $counter = $storage->load($name, $owner);
            if ( ! $this->authoriseUserForCounter( $counter, $this->getLoggedInUser()))
            {
                return $this->jsonErrorResponse('Unauthorized action');
            }
            else
            {
                $counter->reset($comment);
                $storage->store($counter);
                return $this->jsonSuccessResponse(
                    'Counter reset',
                    $counter->toArray()
                );
            }
        });
    }

    /**
     * @param $user
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function showUsersCountersAction($user)
    {
        $userStorage  = $this->getUserStorage();
        $owner = $userStorage->load($user);
        if ( ! $owner)
        {
            $this->addError('User not found');
            return $this->render(
                'default/404.html.twig',
                [],
                Response::HTTP_NOT_FOUND
            );
        }

        $showPrivateCounters = ($this->isLoggedIn() && $owner->isSameAs($this->getLoggedInUser()));

        return $this->render(
            'counter/usersCounters.html.twig',
            [
                'owner' => $owner,
                'counters' => $this->getCounterStorage()->getUsersCounters(
                    $owner->getNick(),
                    $showPrivateCounters
                )
            ]
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
     * @return JsonResponse
     */
    public function deleteAction($name, $owner = null)
    {
        return $this->invokeMethod(function() use ($name, $owner)
        {
            $counter = $this->loadCounter($name, $owner);

            if ( ! $counter)
            {
                return $this->jsonWarningResponse('Counter not found', JsonResponse::HTTP_NOT_FOUND);
            }
            else if ( ! $this->isLoggedIn() || ! $counter->isOwnedBy($this->getLoggedInUser()))
            {
                return $this->jsonErrorResponse("Unauthorized action", JsonResponse::HTTP_UNAUTHORIZED);
            }

            $this->getCounterStorage()->remove($counter);

            return $this->jsonSuccessResponse("Counter removed");
        });
    }
}
