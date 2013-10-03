<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class BaseController extends Controller
{
    /**
     *
     * @var array
     */
    protected $response = array(
        'title' => 'Days Without X'
    );

    protected function applyToResponse(array $array)
    {
        $this->response = array_merge($this->response, $array);
    }

    protected function setTilte($newTitle)
    {
        $this->response['title'] = $newTitle;
    }
}
