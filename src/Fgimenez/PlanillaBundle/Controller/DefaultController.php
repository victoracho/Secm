<?php

namespace Fgimenez\PlanillaBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Fgimenez\TestBundle\Entity\Categoria;

class DefaultController extends Controller {

    public function indexAction() {
        
        $categoria = new Categoria();
        $form_categoria = $this->createForm('Fgimenez\TestBundle\Form\CategoriaType', $categoria);
        //$formCategoria->handleRequest($request);
        
        return $this->render('FgimenezPlanillaBundle:Default:index.html.twig', array(
                    'categoria' => $categoria,
                    'form_categoria' => $form_categoria->createView(),
        ));
    }

    public function menuAction(Request $request) {
        //echo '-----'.$this->container->get('security.token_storage')->getToken()->getUser()->getUsername();
        return $this->render('FgimenezPlanillaBundle:Default:menu.html.twig');
    }

}
