<?php

namespace bulkBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Component\HttpFoundation\Response;
use FOS\UserBundle\FOSUserEvents;
use bulkBundle\Entity\Prueba;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrap3View;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class pruebaController extends Controller
{


      public function pruebaAction(Request $request)
      {

                $prueba = new Prueba();
                $form   = $this->createForm('bulkBundle\Form\pruebaType', $prueba);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                $serializer = new Serializer(array(new DateTimeNormalizer()));

                $fecha = $form['fechaNac']->getdata();

                $fecha = $serializer->denormalize($fecha, \DateTime::class);

                $time = date('Y/m/d');

                $time = $serializer->denormalize($time, \DateTime::class);

                $fecha = date_diff($time, $fecha);

                $prueba->setEdad($fecha->y);

                $prueba->setFechaNac($form['fechaNac']->getdata());

                $ip = $request->getClientIp();

                $prueba->setIp($ip);


               $navegador =  $_SERVER['HTTP_USER_AGENT'];

               $result = explode(" ", $navegador, 2);

                $prueba->setNavegador($result[0]);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($prueba);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('success', "<a> Datos registrados con exito</a>" );

                    $nextAction=  $request->get('submit') == 'save' ? 'bulk_pruebas' : 'bulk_pruebas';

                    return $this->redirectToRoute($nextAction);


                  }

                return $this->render('bulkBundle:correo:prueba.html.twig', array(
                    'prueba' => $prueba,
                    'form'   => $form->createView(),
                ));
            }



                public function pruebasAction(Request $request)
                {

                    $em = $this->getDoctrine()->getManager();
                    $queryBuilder = $em->getRepository('bulkBundle:Prueba')
                            ->createQueryBuilder('e');

                        list($pruebas, $pagerHtml) = $this->paginator($queryBuilder, $request);

                    // se chequea si el arreglo con los permisos del usuarios tiene el rol grupo, si lo tiene quiere decir
                    //que al menos está habilitado
              /*
                      if(in_array("ROLE_HABILITADO", $arr)){
                        $array[$key]='habilitado';
                      } elseif(in_array('ROLE_DESHABILITADO', $arr)){
                        $array[$key]='deshabilitado';
                      } elseif(in_array('ROLE_USER', $arr)){
                        $array[$key]='creado';
                      }
             */


                    return $this->render('bulkBundle:correo:pruebas.html.twig', array(
                                'pruebas' => $pruebas,
                                'pagerHtml' => $pagerHtml,

                    ));
               }




               protected function paginator($queryBuilder, Request $request)
               {
                   //sorting
                   $sortCol = $queryBuilder->getRootAlias().'.'.$request->get('pcg_sort_col', 'id');
                   $queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
                   // Paginator
                   $adapter = new DoctrineORMAdapter($queryBuilder);
                   $pagerfanta = new Pagerfanta($adapter);
                   $pagerfanta->setMaxPerPage($request->get('pcg_show' , 10));

                   try {
                       $pagerfanta->setCurrentPage($request->get('pcg_page', 1));
                   } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
                       $pagerfanta->setCurrentPage(1);
                   }

                   $entities = $pagerfanta->getCurrentPageResults();

                   // Paginator - route generator
                   $me = $this;
                   $routeGenerator = function($page) use ($me, $request)
                   {
                       $requestParams = $request->query->all();
                       $requestParams['pcg_page'] = $page;
                       return $me->generateUrl('pruebas', $requestParams);
                   };

                   // Paginator - view
                   $view = new TwitterBootstrap3View();
                   $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
                       'proximity' => 3,
                       'prev_message' => 'Anterior',
                       'next_message' => 'Siguiente',
                   ));

                   return array($entities, $pagerHtml);
               }
}
