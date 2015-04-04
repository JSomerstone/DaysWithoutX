<?php
namespace JSomerstone\DaysWithout\Controller;

use JSomerstone\DaysWithout\Exception\PublicException,
    JSomerstone\DaysWithout\Exception\SessionException;
use JSomerstone\DaysWithout\Storage\StorageException;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;
use JSomerstone\DaysWithout\Model\CounterModel,
    JSomerstone\DaysWithout\Model\UserModel,
    JSomerstone\DaysWithout\Storage\CounterStorage,
    JSomerstone\DaysWithout\Lib\InputValidatorException;

class ApiController extends BaseController
{
    use SessionTrait;

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function signupAction(Request $request)
    {
        $nick = $request->get('nick');
        $password = $request->get('password');
        $passwordConfirmation = $request->get('password-confirm');

        try
        {
            $this->validateSignup($nick, $password, $passwordConfirmation);
            $user = $this->signUpUser($nick, $password);
            $this->setLoggedInUser($user);
        }
        catch (PublicException $e)
        {
            $this->getLogger()->addNotice((string)$e, array(__CLASS__, __METHOD__, get_class($e)));
            return $this->jsonErrorResponse($e->getMessage());
        }
        catch (\Exception $e)
        {
            $this->getLogger()->addError($e->getMessage(), array(__CLASS__, __METHOD__, get_class($e)));
            $this->addError('Unexpected exception occurred');
            return $this->jsonErrorResponse($e->getMessage());
        }

        return $this->jsonSuccessResponse("Welcome $nick");
    }

    private function validateSignup($nick, $password, $passwordConfirmation)
    {
        $this->getInputValidator()
            ->validateFields(array(
                'nick' => $nick,
                'password' => $password
            ))
            ->validatePassword($password, $passwordConfirmation);
    }

    private function signUpUser($nick, $password)
    {
        $userStorage = $this->getUserStorage();
        if ($userStorage->exists($nick))
        {
            throw new PublicException(
                "Unfortunately nick '$nick' is already taken"
            );
        }
        $user = new UserModel($nick, $password);
        $userStorage->store($user);
        return $user;
    }

    /**
     * @param string $counter
     * @param string $owner
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetAction($counter, $owner = null)
    {
        $storage = $this->getCounterStorage();
        $comment = $this->getRequest()->get('comment') ?: null;
        try
        {
            $this->assertCounterExists($counter, $owner);
            $counterObj = $this->getCounterStorage()->load($counter, $owner);
            if ( ! $counterObj->isPublic() )
            {
                $userObj = $this->getRequestingUser();
                $this->assertAuthorized($counterObj, $userObj);
            }
            if ( $counterObj->isResettable() )
            {
                $counterObj->reset($comment);
                $storage->store($counterObj);
            }
            return $this->jsonSuccessResponse('Counter reset', $this->getUrlForCounter($counterObj));
        }
        catch (PublicException $e)
        {
            $this->getLogger()->notice(get_class($e) . ':' .$e->getMessage());
            return $this->jsonErrorResponse($e->getMessage());
        }
        catch (SessionException $e)
        {
            $this->getLogger()->notice(get_class($e) . ':' .$e->getMessage());
            return $this->jsonErrorResponse('Unauthorized action');
        }
        catch (\Exception $e)
        {
            $this->getLogger()->error(get_class($e) . ':' .$e->getMessage());
            return $this->jsonErrorResponse('System error');
        }
    }

    private function assertCounterExists($counter, $owner)
    {
        if ( ! $this->getCounterStorage()->exists($counter, $owner))
        {
            throw new PublicException('Counter not found');
        }
    }

    private function assertAuthorized(CounterModel $counter, UserModel $user)
    {
        if ( ! $this->authoriseUserForCounter($user, $counter))
        {
            throw new PublicException('Unauthorized action');
        }
    }
}
