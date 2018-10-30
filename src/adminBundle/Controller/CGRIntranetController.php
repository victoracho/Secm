<?php

namespace adminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;
use adminBundle\Entity\biblioteca;
use adminBundle\Form\bibliotecaType;
use Symfony\Component\HttpFoundation\File\File;

class CGRIntranetController extends Controller
{
  /**
   * @Route("biblioteca/ver/{slug1}", name="ver_seccion")
   */
  public function seccionAction($slug1)
  {
    $em = $this->getDoctrine()->getManager();
    $qb = $em->createQueryBuilder();
    $qb->add('select', 'm.id')
       ->add('from', 'adminBundle:tipo_biblioteca m')
       ->add('where', 'm.nombre= ?1')
       ->setParameter(1, $slug1);


   $query = $qb->getQuery();
   $secciones=$query->getResult();


   $secciones=$secciones[0]['id'];

  $qb1= $em->createQueryBuilder();

   $qb1->add('select', 'u')
   ->add('from', 'adminBundle:biblioteca u')
   ->add('where', 'u.tipo_biblioteca= ?1')
   ->setParameter(1 , $secciones);

    $query1 = $qb1->getQuery();
    $items = $query1->getResult();



    return $this->render('adminBundle:Default:index.html.twig',
        array( 'item'=> $items

        )
      );


  }

}
