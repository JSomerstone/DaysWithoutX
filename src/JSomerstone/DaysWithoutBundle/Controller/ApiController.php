<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

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


    /**
     * @param string $counter
     * @param string $owner
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetAction($counter, $owner = null)
    {
        $storage = $this->getCounterStorage();
        $comment = $this->getRequest()->get('comment') ?: null;

        if ( ! $storage->exists($counter, $owner))
        {
            $this->addError('Counter not found');
            return $this->jsonResponse(false, 'Counter not found');
        }
        $counterObj = $storage->load($counter, $owner);

        if ( ! $this->isLoggedIn() && ! $counterObj->isPublic())
        {
            $this->addError('Unauthorized action');
            return $this->jsonResponse(false, 'Unauthorized action');
        }

        if ($counterObj->isPublic() || $counterObj->isOwnedBy($this->getLoggedInUser()))
        {
            if ( $counterObj->isResettable() )
            {
                $counterObj->reset($comment);
                $storage->store($counterObj);
            }
            return $this->jsonResponse(true, 'Counter reset');
        }
        else
        {
            //If unauthorized, do not reveal if exists or not
            $this->addError('Counter not found');
            return $this->jsonResponse(false, 'Noooo, nope');
        }
    }
}
