<?php
namespace JSomerstone\DaysWithout\Controller;

use JSomerstone\DaysWithout\Lib\InputValidatorException;
use JSomerstone\DaysWithout\Model\CounterModel;
use JSomerstone\DaysWithout\Lib\StringFormatter;
use JSomerstone\DaysWithout\Model\UserModel;
use JSomerstone\DaysWithout\Application,
    JSomerstone\DaysWithout\Lib\InputValidator,
    JSomerstone\DaysWithout\Service\AuthenticationServiceProvider;
use JSomerstone\DaysWithout\Exception\PublicException,
    JSomerstone\DaysWithout\Lib\InputValidatorValueException;

use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @param int $httpStatusCode
     * @return mixed
     */
    public function render($view, array $parameters = array(), $httpStatusCode = Response::HTTP_OK)
    {
        $loggedInUser = $this->getSession()->get('user');
        $parameters = array_merge(
            $this->response,
            array(
                'user' => $loggedInUser,
                'loggedIn' => $loggedInUser ? true : false,
                'field' => $this->getInputValidator()->getValidationRules()
            ),
            $parameters
        );

        return new Response(
            $this->get('twig')->render(
                $view,
                $parameters
            ),
            $httpStatusCode
        );
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
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            )
        );
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

    /**
     * @param UserModel $user
     * @param CounterModel $counter
     * @return bool
     */
    protected function authoriseUserForCounter(UserModel $user, CounterModel $counter)
    {
        return $counter->isPublic() || $counter->isOwnedBy($user);
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
        return $this->get('storage.counter');
    }

    /**
     * @param $callable
     * @return Response
     * @throws InvalidArgumentException
     */
    protected function invokeMethod($callable)
    {
        if ( ! is_callable($callable))
        {
            throw new InvalidArgumentException('Unable to invoke controller method');
        }
        try
        {
            return $callable();
        }
        catch (InputValidatorValueException $e)
        {
            $this->getLogger()->addInfo('input validation failed', $e->getData());
            return $this->jsonWarningResponse(
                $e->getMessage(),
                $e->getData(),
                JsonResponse::HTTP_BAD_REQUEST);
        }
        catch (PublicException $e)
        {
            return $this->jsonWarningResponse(
                $e->getMessage(),
                array(),
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
        catch (SessionException $e)
        {
            $this->logException($e);
            return $this->jsonErrorResponse(
                'Unauthorized action',
                array(),
                JsonResponse::HTTP_FORBIDDEN
            );
        }
        catch (\Exception $e)
        {
            $this->logException($e);
            return $this->jsonErrorResponse(
                'System error',
                array(),
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param $success
     * @param string $level
     * @param null $message
     * @param int $status
     * @param array $data
     * @return JsonResponse
     */
    protected function jsonResponse(
        $success,
        $level = 'info',
        $message = null,
        $status = JsonResponse::HTTP_OK,
        $data = array()
    )
    {
        return new JsonResponse(array(
            'success' => $success,
            'message' => $message,
            'level' => $level,
            'status' => $status,
            'data' => $data
        ));
    }

    /**
     * @param $message
     * @param array $data
     * @param int $statusCode HTTP
     * @return JsonResponse
     */
    protected function jsonSuccessResponse(
        $message,
        $data = null,
        $statusCode = JsonResponse::HTTP_OK
    )
    {
        return $this->jsonResponse(
            true,
            'info',
            $message,
            $statusCode,
            $data
        );
    }

    /**
     * @param $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function jsonErrorResponse(
        $message,
        $data = array(),
        $statusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR
    )
    {
        return $this->jsonResponse(
            false,
            'error',
            $message,
            $statusCode,
            $data
        );
    }

    /**
     * @param $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function jsonWarningResponse(
        $message,
        $data = array(),
        $statusCode = JsonResponse::HTTP_BAD_REQUEST
    )
    {
        return $this->jsonResponse(
            false,
            'warning',
            $message,
            $statusCode,
            $data
        );
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
