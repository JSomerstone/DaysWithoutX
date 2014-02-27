<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use Doctrine\ORM\Query\AST\Functions\ConcatFunction;
use JSomerstone\DaysWithoutBundle\Form\Type\CounterType,
    JSomerstone\DaysWithoutBundle\Form\Type\OwnerType,
    JSomerstone\DaysWithoutBundle\Model\CounterModel;
use JSomerstone\DaysWithoutBundle\Form\Type\ResetType;
use JSomerstone\DaysWithoutBundle\Form\Type\UserType;
use JSomerstone\DaysWithoutBundle\Lib\StringFormatter;
use JSomerstone\DaysWithoutBundle\Model\UserModel;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Tests\Core\User\UserTest;

abstract class BaseController extends Controller
{
    /**
     *
     * @var array
     */
    protected $response = array(
        'title' => 'Days Without <X>',
        'messages' => array(),
        'notices' => array(),
        'errors' => array(),
    );

    /**
     * @var JSomerstone\DaysWithoutBundle\Storage\UserStorage
     */
    protected $userStorage;

    protected function bindToResponse($variable, &$value)
    {
        $this->response[$variable] = $value;
    }

    protected function applyToResponse(array $array)
    {
        $this->response = array_merge($this->response, $array);
    }

    protected function addMessage($msg)
    {
        $this->get('session')->getFlashBag()->add(
            'message',
            $msg
        );
    }

    protected function addNotice($msg)
    {
        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );
    }

    protected function addError($msg)
    {
        $this->get('session')->getFlashBag()->add(
            'error',
            $msg
        );
    }

    protected function setTilte($newTitle)
    {
        $this->response['title'] = $newTitle;
    }

    protected function setForm(\Symfony\Component\Form\Form $form)
    {
        $this->response['form'] = $form->createView();
    }

    /**
     *
     * @param string $headline optional
     * @param string $owner optional
     * @return \Symfony\Component\Form\Form
     */
    protected function getCounterForm($headline = null, $owner = null)
    {
        $counter = new CounterModel(StringFormatter::getUrlUnsafe($headline));
        $owner = new UserModel($owner);

        return $this->createFormBuilder($counter)
            ->add('headline', 'text')
            ->add('public', 'submit')
            ->add('owner', new UserType())
            ->add('private', 'submit')
            ->getForm();

    }

    protected function getResetForm(CounterModel $counter)
    {
        $builder = $this->createFormBuilder(new UserModel());
        if ( ! $counter->isPublic() )
        {
            $builder->add('nick', 'text')
                ->add('password', 'password');
        }
        $builder->add('reset', 'submit');
        return $builder->getForm();
    }

    /**
     * @return JSomerstone\DaysWithoutBundle\Storage\UserStorage|object
     */
    protected function getUserStorage()
    {
        if ( ! $this->userStorage) {
            $this->userStorage = $this->get('dayswithout.storage.user');
        }
        return $this->userStorage;
    }

    protected function getLoginForm()
    {
        return $this->createFormBuilder(new UserModel())
            ->add('nick', 'text')
            ->add('password', 'password')
            ->add('login', 'submit')
            ->getForm();
    }
}
