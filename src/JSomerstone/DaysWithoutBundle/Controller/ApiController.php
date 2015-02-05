<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use JSomerstone\DaysWithoutBundle\Exception\PublicException,
    JSomerstone\DaysWithoutBundle\Exception\SessionException;
use JSomerstone\DaysWithoutBundle\Storage\StorageException;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\DependencyInjection\ContainerInterface as Container,
    Symfony\Component\Form\Form as Form;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Model\UserModel,
    JSomerstone\DaysWithoutBundle\Storage\CounterStorage,
    JSomerstone\DaysWithoutBundle\Lib\InputValidatorException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiController extends BaseController
{
    use SessionTrait;

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
