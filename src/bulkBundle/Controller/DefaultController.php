<?php

namespace bulkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('bulkBundle:Default:index.html.twig');
    }
}
