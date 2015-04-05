<?php
namespace JSomerstone\DaysWithout\Controller;

use JSomerstone\DaysWithout\Model\CounterModel;
use JSomerstone\DaysWithout\Lib\StringFormatter;
use JSomerstone\DaysWithout\Model\UserModel;
use JSomerstone\DaysWithout\Application,
    JSomerstone\DaysWithout\Lib\InputValidator;

use Symfony\Component\HttpFoundation\Response;

abstract class BaseController
{
    /**
     * @var Application
     */
    protected $app;

    /**
     *
     * @var array
     */
    protected $response = array(
        'title' => '',
        'messages' => array(),
        'notices' => array(),
        'errors' => array(),
        'field' => array()
    );

    /**
     * @var JSomerstone\DaysWithout\Storage\UserStorage
     */
    protected $userStorage;

    /**
     * @var JSomerstone\DaysWithout\Storage\CounterStorage
     */
    protected $counterStorage;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    public function register(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get an object from context
     * @param string $name
     * @return mixed
     */
    protected function get($name)
    {
        return $this->app[$name];
    }

    /**
     * @param $view
     * @param array $parameters
     * @return mixed
     */
    public function render($view, array $parameters = array())
    {
        $loggedInUser = $this->getSession()->get('user');
        $parameters['user'] = $loggedInUser;
        $parameters['loggedIn'] = $loggedInUser ? true : false;
        $this->setValidationRulesForView($parameters);

        return $this->get('twig')->render(
            $view,
            $parameters
        );
    }

    private function setValidationRulesForView(&$parameters)
    {
        $validationRules = $this->getInputValidator()->getValidationRules();
        foreach($validationRules as $fieldName => $rules)
        {
            $parameters['field'][$fieldName] = $rules;
        }
    }

    /**
     * @param string $variable Index of the response-parameter to set
     * @param $value Value of response-parameter _as_reference_
     */
    protected function bindToResponse($variable, &$value)
    {
        $this->response[$variable] = $value;
    }

    /**
     * @param string $variable Index of the response-parameter to set
     * @param mixed $value Value of response-parameter
     */
    protected function setToResponse($variable, $value)
    {
        $this->response[$variable] = $value;
    }

    /**
     * @param array $array Array to merge with current response
     */
    protected function applyToResponse(array $array)
    {
        $this->response = array_merge($this->response, $array);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession()
    {
        return $this->get('session');
    }

    /**
     * @return \Monolog\Logger
     */
    protected function getLogger()
    {
        return $this->get('monolog');
    }

    protected function logException(\Exception $e)
    {
        $this->getLogger()->addAlert(
            $e->getMessage(),
            array(
                'controller' => __CLASS__,
                'method' => __METHOD__
            )
        );

        if( true === $this->get('debug'))
        {
            $this->getLogger()->addDebug(
                $e->getMessage(),
                array(
                    'controller' => __CLASS__,
                    'method' => __METHOD__,
                    'callstack' => get_call_stack()
                )
            );
        }
    }

    protected function addMessage($msg)
    {
        $this->getSession()->getFlashBag()->add(
            'message',
            $msg
        );
        return $this;
    }

    protected function addNotice($msg)
    {
        $this->getSession()->getFlashBag()->add(
            'notice',
            $msg
        );
        return $this;
    }

    protected function addError($msg)
    {
        $this->getSession()->getFlashBag()->add(
            'error',
            $msg
        );
        return $this;
    }

    protected function setTilte($newTitle)
    {
        $this->response['title'] = $newTitle;
        return $this;
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
            || ! $this->authoriseUserForCounter($loggedInUser, $counter))
        {
            $builder->add('nick', 'text')
                ->add('password', 'password');
        }
        return $builder->getForm();
    }

    /**
     * @return \JSomerstone\DaysWithout\Storage\UserStorage
     */
    protected function getUserStorage()
    {
        if ( ! $this->userStorage) {
            $this->userStorage = $this->get('storage.user');
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
     * @param CounterModel $counter
     * @return bool
     */
    protected function authoriseUserForCounter(UserModel $user, CounterModel $counter)
    {
        return $this->get('dayswithout.service.authentication')
            ->authoriseUserForCounter($user, $counter);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function getFrontPageRedirection()
    {
        return $this->redirect($this->generateUrl('dwo_frontpage'));
    }

    /**
     * @return InputValidator
     */
    protected function getInputValidator()
    {
        return $this->get('validator');
    }

    /**
     *
     * @return \JSomerstone\DaysWithout\Storage\CounterStorage
     */
    protected function getCounterStorage()
    {
        if ( ! isset($this->counterStorage)) {
            $this->counterStorage = $this->get('storage.counter');
        }
        return $this->counterStorage;
    }

    /**
     * @param $success
     * @param null $message
     * @param null $redirUrl
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function jsonResponse($success, $message = null, $redirUrl = null)
    {
        $jsonResponse = new Response();
        $jsonResponse->setContent(json_encode([
            'success' => $success,
            'message' => $message,
            'redirection' => $redirUrl
        ]));
        return $jsonResponse;
    }

    /**
     * @param $message
     * @param null $redirect
     * @return Response
     */
    protected function jsonSuccessResponse($message, $redirect = null)
    {
        $this->addMessage($message);
        return $this->jsonResponse(true, $message, $redirect);
    }
    /**
     * @param $message
     * @param null $redirect
     * @return Response
     */
    protected function jsonErrorResponse($message, $redirect = null)
    {
        $this->addError($message);
        return $this->jsonResponse(false, $message, $redirect);
    }

    /**
     * @param CounterModel $counterModel
     * @return string
     */
    protected function getUrlForCounter(CounterModel $counterModel)
    {
        return $this->generateUrl(
            'dwo_show_counter',
            array(
                'name' => $counterModel->getName(),
                'owner' => $counterModel->isPublic() ? null : $counterModel->getOwnerId()
            )
        );
    }
}
