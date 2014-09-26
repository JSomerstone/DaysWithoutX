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
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\HttpFoundation\Response;

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
        'field' => array()
    );

    /**
     * @var JSomerstone\DaysWithoutBundle\Storage\UserStorage
     */
    protected $userStorage;

    public function render($view, array $parameters = array(), Response $response = null)
    {
        $loggedInUser = $this->get('session')->get('user');
        $parameters['user'] = $loggedInUser;
        $parameters['loggedIn'] = $loggedInUser ? true : false;
        $this->setValidationRulesForView($parameters);

        return parent::render($view, $parameters, $response);
    }

    private function setValidationRulesForView(&$parameters)
    {
        $validationRules = $this->getInputValidator()->getValidationRules();
        foreach($validationRules as $fieldName => $rules)
        {
            $parameters['field'][$fieldName]['pattern'] = $rules['pattern'];
            $parameters['field'][$fieldName]['title'] = $rules['message'];
        }
    }

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
     * @param bool $loggedIn True if user is logged in, false otherwise
     * @return \Symfony\Component\Form\Form
     */
    protected function getCounterForm($headline = null, $loggedIn = false)
    {
        $counter = new CounterModel(StringFormatter::getUrlUnsafe($headline));

        $builder = $this->createFormBuilder($counter)
            ->add('headline', 'text')
            ->add('public', 'submit')
            ->add('private', 'submit');

        if ( ! $loggedIn)
        {
            $builder->add('owner', new UserType());
        }
        return $builder->getForm();
    }

    protected function getResetForm(CounterModel $counter, UserModel $loggedInUser = null)
    {
        $builder = $this->createFormBuilder(new UserModel());
        $builder->add('reset', 'submit');
        if ($counter->isPublic())
        {
            return $builder->getForm();
        }

        if ( is_null($loggedInUser)
            || ! $this->authenticateUserForCounter($loggedInUser, $counter))
        {
            $builder->add('nick', 'text')
                ->add('password', 'password');
        }
        return $builder->getForm();
    }

    /**
     * @return JSomerstone\DaysWithoutBundle\Storage\UserStorage
     */
    protected function getUserStorage()
    {
        if ( ! $this->userStorage) {
            $this->userStorage = $this->get('dayswithout.storage.user');
        }
        return $this->userStorage;
    }

    protected function getLoginForm($loggedIn = false)
    {
        return $this->createFormBuilder(new UserModel())
            ->add('nick', 'text')
            ->add('password', 'password')
            ->add('login', 'submit')
            ->getForm();
    }

    /**
     * @param UserModel $user
     * @return bool
     */
    protected  function authenticateUser(UserModel $user)
    {
        return $this->get('dayswithout.service.authentication')
            ->authenticateUser($user);
    }

    protected function setLoggedInUser(UserModel $user = null)
    {
        return $this->get('session')->set('user', $user);
    }

    protected function getLoggedInUser()
    {
        return $this->get('session')->get('user');
    }

    protected function isLoggedIn()
    {
        return \is_a($this->getLoggedInUser(), 'JSomerstone\DaysWithoutBundle\Model\UserModel');
    }

    /**
     * @param UserModel $user
     * @param CounterModel $counter
     * @return bool
     */
    protected function authenticateUserForCounter(UserModel $user, CounterModel $counter)
    {
        if ($counter->isPublic())
        {
            return true;
        }
        return $this->get('dayswithout.service.authentication')
            ->authenticateUserForCounter($user, $counter);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function getFrontPageRedirection()
    {
        return $this->redirect($this->generateUrl('dwo_frontpage'));
    }

    /**
     * @return JSomerstone\DaysWithoutBundle\Lib\InputValidator
     */
    protected function getInputValidator()
    {
        return $this->get('dayswithout.inputvalidator');
    }
}
