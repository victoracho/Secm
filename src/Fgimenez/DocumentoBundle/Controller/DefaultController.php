<?php

namespace Fgimenez\DocumentoBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FgimenezDocumentoBundle:Default:index.html.twig');
    }
    
     public function menuAction(Request $request) {      
        return $this->render(
               'FgimenezDocumentoBundle:Default:menu.html.twig'
        );
    }
}
