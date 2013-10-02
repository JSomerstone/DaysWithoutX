<?php

namespace JSomerstone\DaysWithoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('JSomerstoneDaysWithoutBundle:Default:index.html.twig', array('name' => $name));
    }
}
