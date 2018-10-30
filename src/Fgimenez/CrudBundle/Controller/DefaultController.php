<?php

namespace Fgimenez\CrudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FgimenezCrudBundle:Default:index.html.twig');
    }
}
