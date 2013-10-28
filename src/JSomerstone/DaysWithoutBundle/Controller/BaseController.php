<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use Doctrine\ORM\Query\AST\Functions\ConcatFunction;
use JSomerstone\DaysWithoutBundle\Form\Type\CounterType,
    JSomerstone\DaysWithoutBundle\Form\Type\OwnerType,
    JSomerstone\DaysWithoutBundle\Model\CounterModel;
use JSomerstone\DaysWithoutBundle\Form\Type\ResetType;
use JSomerstone\DaysWithoutBundle\Lib\StringFormatter;
use JSomerstone\DaysWithoutBundle\Model\UserModel;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class BaseController extends Controller
{
    /**
     *
     * @var array
     */
    protected $response = array(
        'title' => 'Days Without X',
        'messages' => array(),
        'notices' => array(),
        'errors' => array(),
    );

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
     * @return \Symfony\Component\Form\Form
     */
    protected function getCounterForm($headline = null, $owner = null)
    {
        $counter = new CounterModel(StringFormatter::getUrlUnsafe($headline));

        return $this->createForm(
            new CounterType(),
            $counter,
            array(
                'action' => $this->generateUrl('dwo_create_counter'),
                'method' => 'POST',
            )
        );

    }
}
