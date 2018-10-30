<?php

namespace Fgimenez\TestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {

    public function indexAction() {
        return $this->render('FgimenezTestBundle:Default:index.html.twig');
    }

    public function menuAction(Request $request) {      
        return $this->render(
               'FgimenezTestBundle:Default:menu.html.twig'
        );
    }

}
