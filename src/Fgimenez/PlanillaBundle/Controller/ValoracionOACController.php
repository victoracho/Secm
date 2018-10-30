<?php

namespace Fgimenez\PlanillaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;
use Fgimenez\PlanillaBundle\Entity\ValoracionOAC;
use Fgimenez\PlanillaBundle\Entity\Planilla;
use jsanchez\InvolucradosBundle\Repository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Fgimenez\PlanillaBundle\Entity\ValoracionReferenciasNormativas;
use Mmartin4\MantenimientoBundle\Entity\Referencias_Normativas;
use Fgimenez\PlanillaBundle\Entity\Estatus;
use Fgimenez\PlanillaBundle\Entity\EstatusPlanilla;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * ValoracionOAC controller.
 *
 */
class ValoracionOACController extends Controller {

    /**
     * Lists all ValoracionOAC entities.
     *
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('FgimenezPlanillaBundle:ValoracionOAC')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($valoracionOACs, $pagerHtml) = $this->paginator($queryBuilder, $request);

        return $this->render('valoracionoac/index.html.twig', array(
                    'valoracionOACs' => $valoracionOACs,
                    'pagerHtml' => $pagerHtml,
                    'filterForm' => $filterForm->createView(),
        ));
    }

    /**
     * Create filter form and process filter request.
     *
     */
    protected function filter($queryBuilder, Request $request) {
        $session = $request->getSession();
        $filterForm = $this->createForm('Fgimenez\PlanillaBundle\Form\ValoracionOACFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('ValoracionOACControllerFilter');
        }

        // Filter action
        if ($request->get('filter_action') == 'filter') {
            // Bind values from the request
            $filterForm->handleRequest($request);

            if ($filterForm->isValid()) {
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                // Save filter to session
                $filterData = $filterForm->getData();
                $session->set('ValoracionOACControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('ValoracionOACControllerFilter')) {
                $filterData = $session->get('ValoracionOACControllerFilter');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('Fgimenez\PlanillaBundle\Form\ValoracionOACFilterType', $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }

    /**
     * Get results from paginator and get paginator view.
     *
     */
    protected function paginator($queryBuilder, Request $request) {
        //sorting
        //$sortCol = $queryBuilder->getRootAlias() . '.' . $request->get('pcg_sort_col', 'id');
        $sortCol = $queryBuilder->getRootAlias() . '.' . $request->get('pcg_sort_col', 'fechaUltimoStatus');
        $queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($request->get('pcg_show', 10));

        try {
            $pagerfanta->setCurrentPage($request->get('pcg_page', 1));
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
            $pagerfanta->setCurrentPage(1);
        }

        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me, $request) {
            $requestParams = $request->query->all();
            $requestParams['pcg_page'] = $page;
            return $me->generateUrl('valoracionoac', $requestParams);
        };

        // Paginator - view
        $view = new TwitterBootstrap3View();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => 'previous',
            'next_message' => 'next',
        ));

        return array($entities, $pagerHtml);
    }

    /**
     * Displays a form to create a new ValoracionOAC entity.
     *
     */
    public function newAction(Request $request, Planilla $planilla) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_VALORARCASO')) {

            throw $this->createAccessDeniedException();
        }


        $valoracionOAC = new ValoracionOAC();

        $form = $this->createForm('Fgimenez\PlanillaBundle\Form\ValoracionOACType', $valoracionOAC);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $aux_id_detalle = "";

        if ($form->isSubmitted() && $form->isValid()) {

            $accion = $request->get('submit');

            if ($form['id_ente']->getdata() != "") {

                $valoracionOAC->setEnteOrgano($form['id_ente']->getdata());
            }

            $valoracionOAC->setOrganismoCompetencia($request->get('id_detalle_competencia'));

            $valoracionOAC->setValoracionOACPlanilla($planilla);

            $array_referencia_normativa = $request->get('valores');

            if (count($array_referencia_normativa) == 0) {

                $this->get('session')->getFlashBag()->add('danger', 'Debe registrar una referencia normativa');

                return $this->render('FgimenezPlanillaBundle:Valoracionoac:new.html.twig', array(
                            'valoracionOAC' => $valoracionOAC,
                            'planilla' => $planilla,
                            'form' => $form->createView(),
                            'aux_id_detalle' => $request->get('id_detalle_competencia'),
                ));
            }

            $valoracionOAC->setUsuarioCreacion($this->getUser()->getUsername());

            $em->persist($valoracionOAC);

            foreach ($array_referencia_normativa as $referencia) {

                $valoracionOAC_referenciaNormativa = new ValoracionReferenciasNormativas();

                $resultado = explode("#", $referencia);

                $id_referencia_normativa = $resultado[0];

                $obj_referencia_normativa = $em->getRepository('Mmartin4MantenimientoBundle:Referencias_Normativas')->findOneById($id_referencia_normativa);

                $valoracionOAC_referenciaNormativa->setReferenciasNormativas($obj_referencia_normativa);

                $valoracionOAC_referenciaNormativa->setValoracionOAC($valoracionOAC);

                $valoracionOAC_referenciaNormativa->setArticulo($resultado[1]);

                if ($resultado[2] != "") {

                    $valoracionOAC_referenciaNormativa->setNumeral($resultado[2]);
                }

                if ($resultado[3] != "") {

                    $valoracionOAC_referenciaNormativa->setLiteral($resultado[3]);
                }

                $valoracionOAC->addValoracionOACReferenciasNormativa($valoracionOAC_referenciaNormativa);
            }

            if ($accion == 'finalizar_valoracion') {

                $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(5);

                $editLink = $this->generateUrl('valoracion_pdf', array('id' => $valoracionOAC->getValoracionOACPlanilla()->getId()));

                $pdfPlanilla = '<script>var strWindowFeatures = "location=yes,height=570,width=520,scrollbars=yes,status=yes"; window.open("' . 'http://' . $_SERVER['SERVER_NAME'] . $editLink . '", "_blank", null);</script>';

                $mensaje = 'La valoración del caso' . $valoracionOAC->getValoracionOACPlanilla()->getCodigo() . ' se ha realizado exitosamente' . $pdfPlanilla;


            } else {

                $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(4);

                $mensaje = 'Los cambios en el caso' . $planilla->getCodigo() . ' se han guardado exitosamente';
            }

            $planilla = $this->registrarEstatusPlanilla($planilla, $estatus);

            $planilla->setUsuarioModificacion($this->getUser()->getUsername());

            $em->persist($planilla);

            $em->persist($valoracionOAC);

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $mensaje);


            return $this->redirectToRoute('planilla');
        }


        return $this->render('FgimenezPlanillaBundle:Valoracionoac:new.html.twig', array(
                    'valoracionOAC' => $valoracionOAC,
                    'planilla' => $planilla,
                    'form' => $form->createView(),
                    'aux_id_detalle' => $aux_id_detalle,
        ));
    }

    /**
     * Finds and displays a ValoracionOAC entity.
     *
     */
    public function showAction(ValoracionOAC $valoracionOAC) {
        $deleteForm = $this->createDeleteForm($valoracionOAC);

        $em = $this->getDoctrine()->getManager();

        $id_ente = $valoracionOAC->getEnteOrgano();

        $ente_organo = "";

        if ($id_ente != "") {

            $ente_organo = $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($id_ente);
        }

        if ($valoracionOAC->getCompetencia() == 1) {

            $competencia = $em->getRepository('OrganizacionesBundle:organizaciones')->findOneById($valoracionOAC->getOrganismoCompetencia());

            $compete = $competencia->getNombre() . " (CGR)";
        } else {

            $competencia = $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($valoracionOAC->getOrganismoCompetencia());

            $compete = $competencia->getDesEnte() . " (Ente Externo)";
        }

        $funcion = $em->getRepository("FgimenezPlanillaBundle:ValoracionOAC");


        $analista = $funcion->analistaValoracion($valoracionOAC->getValoracionOACPlanilla()->getId());

        return $this->render('FgimenezPlanillaBundle:Valoracionoac:show.html.twig', array(
                    'valoracionOAC' => $valoracionOAC,
                    'delete_form' => $deleteForm->createView(),
                    'ente_organo' => $ente_organo,
                    'competencia' => $compete,
                    'analista' => $analista['nombre_analista'],
                    'creacion' => $analista['created'],
        ));
    }

    /**
     * Displays a form to edit an existing ValoracionOAC entity.
     *
     */
    public function editAction(Request $request, ValoracionOAC $valoracionOAC) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_VALORARCASO')) {

            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm($valoracionOAC);
        $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\ValoracionOACType', $valoracionOAC);
        $editForm->handleRequest($request);

        $accion = $request->get('submit');

        if ($accion == "") {
            $id_ente = $valoracionOAC->getEnteOrgano();
            $id_compete = $valoracionOAC->getOrganismoCompetencia();
            $correcciones = $valoracionOAC->getCorrecciones();
        } else {
            $id_ente = $editForm['id_ente']->getdata();
            $id_compete = $request->get('id_detalle_competencia');
        }

        if ($id_ente != "") {

            $enteOrgano = $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($id_ente);
        } else {

            $enteOrgano = "";
        }

                // para el autocomplete de competencia
        if ($valoracionOAC->getCompetencia() == 1) {

             $auxiliar ="organizacion";
            $competencia = $em->getRepository('OrganizacionesBundle:organizaciones')->findOneById($id_compete);

            $compete = $competencia->getNombre();
        } else {
            $auxiliar= "ente";
            $competencia = $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($id_compete);

            $compete = $competencia->getDesEnte();

        }


        if ($editForm->isSubmitted() && $editForm->isValid()) {
                      //funcion para eliminar caracteres especiales de una cadena
                    function eliminar_simbolos($string){

                        $string = trim($string);

                        $string = str_replace(
                            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
                            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
                            $string
                        );

                        $string = str_replace(
                            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
                            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
                            $string
                        );

                        $string = str_replace(
                            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
                            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
                            $string
                        );

                        $string = str_replace(
                            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
                            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
                            $string
                        );

                        $string = str_replace(
                            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
                            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
                            $string
                        );

                        $string = str_replace(
                            array('ñ', 'Ñ', 'ç', 'Ç'),
                            array('n', 'N', 'c', 'C',),
                            $string
                        );

                        $string = str_replace(
                            array("\\", "¨", "º", "-", "~",
                                 "#", "@", "|", "!", "\"",
                                 "·", "$", "%", "&", "/",
                                 "(", ")", "?", "'", "¡",
                                 "¿", "[", "^", "<code>", "]",
                                 "+", "}", "{", "¨", "´",
                                 ">", "< ", ";", ",", ":",
                                 ".", " "),
                            ' ',
                            $string
                        );
                    return $string;
                    }

            //funcion para  quitar espacios en blanco de una cadena

          function limpia_espacios($cadena){
          	$cadena = str_replace(' ', '', $cadena);
          	return $cadena;
          }


          //campo oculto
                      $idCompete= $request->get('id_detalle_competencia');
                      $competencia= $editForm['organismoCompetencia']->getdata();
                      $idEnte=$editForm['id_ente']->getdata();

          // se verifica si el campo oculto esta vacio, si está vacio quiere decir que no se selecciona de la lista
                      if ($idEnte =="" or $idCompete ==""){
                      $editForm['id_ente']->setdata($valoracionOAC->getEnteOrgano());
                      $this->get('session')->getFlashBag()->add('danger', 'Debe seleccionar un Ente o Dirección general de la lista');

                      return $this->render('FgimenezPlanillaBundle:Valoracionoac:edit.html.twig', array(
                      'valoracionOAC' => $valoracionOAC,
                      'form' => $editForm->createView(),
                      'delete_form' => $deleteForm->createView(),
                      'ente_organo' => $enteOrgano,
                      'competencia' => $compete,
                      'correcciones' => $valoracionOAC->getCorrecciones(),
                            ));
                            }

                     // para saber si el autocomplete de competencia es un ente o una direccion de la cgr
                     if($auxiliar=="organizacion"){
                     $competeid= $em->getRepository('OrganizacionesBundle:organizaciones')->findOneById($idCompete);
                     $competeid= $competeid->getNombre();
                     } else if($auxiliar=="ente") {
                       $competeid = $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($idCompete);
                       $competeid = $competeid->getDesEnte();
                     }

                     $organo= $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($idEnte);
                     $organo = $organo->getDesEnte();

                     $ente = $editForm['enteOrgano']->getdata();
                     $ente = strtolower($ente);
                     $ente = eliminar_simbolos($ente);
                     $ente = limpia_espacios($ente);
                     $ente = mb_strtolower($ente);

                     $organo = strtolower ($organo);
                     $organo= eliminar_simbolos($organo);
                     $organo = limpia_espacios($organo);
                     $organo = mb_strtolower($organo);

                     $competencia= strtolower($competencia);
                     $competencia= eliminar_simbolos($competencia);
                     $competencia=limpia_espacios($competencia);
                     $competencia= mb_strtolower($competencia);

                     $competeid= strtolower($competeid);
                     $competeid= eliminar_simbolos($competeid);
                     $competeid= limpia_espacios($competeid);
                     $competeid= mb_strtolower($competeid);



                        if($ente != $organo or $competencia!=$competeid){
                          // $arr = $em->getRepository('FgimenezPlanillaBundle:ValoracionReferenciasNormativas')->findByIdValoracion($valoracionOAC->getId());

                        $this->get('session')->getFlashBag()->add('danger', 'Debe seleccionar un Ente Externo de la lista');
                        return $this->render('FgimenezPlanillaBundle:Valoracionoac:edit.html.twig', array(
                                    'valoracionOAC' => $valoracionOAC,
                                    'form' => $editForm->createView(),
                                    'delete_form' => $deleteForm->createView(),
                                    'ente_organo' => $enteOrgano,
                                    'competencia' => $compete,
                                  //  'arr' => $arr,
                                    'correcciones' => $valoracionOAC->getCorrecciones(),

                                                                                        ));
                                             }

            $accion = $request->get('submit');


            $valoracionOAC->setEnteOrgano($idEnte);


            $valoracionOAC->setOrganismoCompetencia($request->get('id_detalle_competencia'));

            // se obtienen las referencias de la tabla dinamica
            $array_referencia_normativa = $request->get('valores');

            $valoracionOAC->setUsuarioCreacion($this->getUser()->getUsername());

            //se deben eliminar los registros de la tabla intermedia para luego
            //insertar los que estan en la tabla dinamica del formulario y asi se garantiza que no
            //se dupliquen los registros de las leyes normativas

            $array_objeto_referencia = $em->getRepository('FgimenezPlanillaBundle:ValoracionReferenciasNormativas')->findByIdValoracion($valoracionOAC->getId());



            foreach ($array_objeto_referencia as $objeto_referencia) {
                   $em->remove($objeto_referencia);

            }

            if (count($array_referencia_normativa) == 0) {

                $this->get('session')->getFlashBag()->add('danger', 'Debe registrar una referencia normativa');

                return $this->render('FgimenezPlanillaBundle:Valoracionoac:edit.html.twig', array(
                            'valoracionOAC' => $valoracionOAC,
                            'form' => $editForm->createView(),
                            'delete_form' => $deleteForm->createView(),
                            'ente_organo' => $enteOrgano,
                            'competencia' => $compete,
                            'correcciones' => $valoracionOAC->getCorrecciones(),
                ));
            }

            foreach ($array_referencia_normativa as $referencia) {

                $valoracionOAC_referenciaNormativa = new ValoracionReferenciasNormativas();

                $resultado = explode("#", $referencia);

                $id_referencia_normativa = $resultado[0];

                $obj_referencia_normativa = $em->getRepository('Mmartin4MantenimientoBundle:Referencias_Normativas')->findOneById($id_referencia_normativa);

                $valoracionOAC_referenciaNormativa->setReferenciasNormativas($obj_referencia_normativa);

                $valoracionOAC_referenciaNormativa->setValoracionOAC($valoracionOAC);

                $valoracionOAC_referenciaNormativa->setArticulo($resultado[1]);

                if ($resultado[2] != "") {

                    $valoracionOAC_referenciaNormativa->setNumeral($resultado[2]);
                }

                if ($resultado[3] != "") {

                    $valoracionOAC_referenciaNormativa->setLiteral($resultado[3]);
                }

                $valoracionOAC->addValoracionOACReferenciasNormativa($valoracionOAC_referenciaNormativa);
            }

            if ($accion == 'finalizar_valoracion') {

                $valoracionOAC->setCorrecciones('');

                $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(5);

                $editLink = $this->generateUrl('valoracion_pdf', array('id' => $valoracionOAC->getValoracionOACPlanilla()->getId()));

                $pdfPlanilla = '<script>var strWindowFeatures = "location=yes,height=570,width=520,scrollbars=yes,status=yes"; window.open("' . 'http://' . $_SERVER['SERVER_NAME'] . $editLink . '", "_blank", null);</script>';

                $mensaje = 'La valoración del caso' . $valoracionOAC->getValoracionOACPlanilla()->getCodigo() . ' se ha realizado exitosamente' . $pdfPlanilla;


            }
            if ($accion == 'guardar_cambios') {

                $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(4);

                $mensaje = 'Los cambios en el caso' . $valoracionOAC->getValoracionOACPlanilla()->getCodigo() . ' se han guardado exitosamente';
            }
            if ($accion == 'corregir') {

                $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(6);

                $mensaje = 'Las correcciones del caso' . $valoracionOAC->getValoracionOACPlanilla()->getCodigo() . ' se han guardado exitosamente';
            }

            $valoracionOAC->setUsuarioModificacion($this->getUser()->getUsername());

            $planilla = $valoracionOAC->getValoracionOACPlanilla();

            $planilla = $this->registrarEstatusPlanilla($planilla, $estatus);

            $planilla->setUsuarioModificacion($this->getUser()->getUsername());

            $em->persist($planilla);

            $em->persist($valoracionOAC);

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $mensaje);

            return $this->redirectToRoute('planilla');
        }

        return $this->render('FgimenezPlanillaBundle:Valoracionoac:edit.html.twig', array(
                    'valoracionOAC' => $valoracionOAC,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'ente_organo' => $enteOrgano,
                    'competencia' => $compete,
                    'correcciones' => $correcciones,
        ));
    }

    /**
     * Deletes a ValoracionOAC entity.
     *
     */
    public function deleteAction(Request $request, ValoracionOAC $valoracionOAC) {

        $form = $this->createDeleteForm($valoracionOAC);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($valoracionOAC);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'ValoracionOAC fue eliminado exitosamente');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con ValoracionOAC');
        }

        return $this->redirectToRoute('valoracionoac');
    }

    /**
     * Creates a form to delete a ValoracionOAC entity.
     *
     * @param ValoracionOAC $valoracionOAC The ValoracionOAC entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ValoracionOAC $valoracionOAC) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('valoracionoac_delete', array('id' => $valoracionOAC->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * Delete ValoracionOAC by id
     *
     */
    public function deleteByIdAction(ValoracionOAC $valoracionOAC) {
        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($valoracionOAC);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'ValoracionOAC fue eliminado exitosamente');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con ValoracionOAC');
        }

        return $this->redirect($this->generateUrl('valoracionoac'));
    }

    /**
     * Bulk Action
     */
    public function bulkAction(Request $request) {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('FgimenezPlanillaBundle:ValoracionOAC');

                foreach ($ids as $id) {
                    $valoracionOAC = $repository->find($id);
                    $em->remove($valoracionOAC);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'valoracionOACs was deleted successfully!');
            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the valoracionOACs ');
            }
        }

        return $this->redirect($this->generateUrl('valoracionoac'));
    }

    //funcion de los autocompletar de valoracion para los campos referencias normativas y ente competente,
    //el campo ente u organo se carga desde la ruta de involucrados

    public function autocompletarAction(Request $request) {

        $resultado = "";

        $campo = $request->get('campo');

        $nombre = $request->get('valor');


        $longitud = strlen($nombre);

        if ($longitud > 2) {

            $em = $this->getDoctrine()->getManager();

            $funcion = $em->getRepository("FgimenezPlanillaBundle:ValoracionOAC");

            if ($campo == "competencia") {

                $competencia = $request->get('competencia');

                $resultado = $funcion->descripcionCompetencia($nombre, $competencia);

            }

            if ($campo == "referencia") {

                $resultado = $funcion->descripcionReferenciaNormativa($nombre);

            }
        }


        $response = new JsonResponse();

        $response->setData($resultado);

        $response->headers->set('Content-Type', 'application/json');


        return $response;
    }

    //funcion de los autocompletar de valoracion para los campos referencias normativas y ente competente,
    //el campo ente u organo se carga desde la ruta de involucrados

    public function validarefAction(Request $request) {

    //funcion que valida las referencias

        $campo = $request->get('valor');

        $em = $this->getDoctrine()->getManager();

        $resultado=$em->getRepository('Mmartin4MantenimientoBundle:Referencias_Normativas')->findOneByNombre($campo);

        $response = new JsonResponse();

        $response->setData($resultado);

        $response->headers->set('Content-Type', 'application/json');


        return $response;

    }

    /*     * *FUNCION QUE ACTUALIZA TODOS LOS ESTATUS DE PLANILLA EN LA TABLA ESTATUS_DOCUMENTO*** */

    public function registrarEstatusPlanilla(Planilla $planilla, Estatus $estatus) {

        $em = $this->getDoctrine()->getManager();

        $estatus_planilla = new EstatusPlanilla();

        $estatus_planilla->setPlanilla($planilla);

        $estatus_planilla->setEstatus($estatus);

        $estatus_planilla->setAnalistaAsignado($planilla->getAnalistaAsignado());

        $planilla->setUltimoStatus($estatus);

        $planilla->setFechaUltimoStatus(new \DateTime());

        $planilla->addPlanillaEstatus($estatus_planilla);

        return $planilla;
    }

    public function evaluarValoracionAction(Request $request, ValoracionOAC $valoracionOAC) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_EVALUAR')) {

            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm($valoracionOAC);
        $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\ValoracionOACType', $valoracionOAC);
        //$editForm->handleRequest($request);

        $editForm->add('correcciones');

        $nombre_analista = "";

        $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        list($comboAnalista, $combo_analista2) = $planilla_repo->comboAnalista();

        $analista = $valoracionOAC->getValoracionOACPlanilla()->getAnalistaAsignado();

        $valoracionOAC->setAnalistaAsignado($analista);

        $editForm->add('analista_asignado', ChoiceType::class, array(
            'label' => 'Analista',
            'required' => false,
            'choices' => $comboAnalista,
        ));

        $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        list($combo_analista1, $combo_analista2) = $planilla_repo->comboAnalista();

        $accion = $request->get('submit');

        $ente_organo = "";

        $id_ente = $valoracionOAC->getEnteOrgano();

        $id_compete = $valoracionOAC->getOrganismoCompetencia();

        if ($id_ente != "") {

            $enteOrgano = $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($id_ente);
        }

        if ($valoracionOAC->getCompetencia() == 1) {

            $competencia = $em->getRepository('OrganizacionesBundle:organizaciones')->findOneById($id_compete);

            $compete = $competencia->getNombre();
        } else {

            $competencia = $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($id_compete);

            $compete = $competencia->getDesEnte();
        }

        if ($accion != '') {

            if ($accion == 'corregir') {

                $formulario = $request->get('valoracion_oac');

                $correcciones = $formulario['correcciones'];

                $analista_corregir = $formulario['analista_asignado'];

                if ($correcciones == "" || $analista_corregir == "") {

                    $this->get('session')->getFlashBag()->add('danger', "Debe indicar las correcciones y el analista que las debe realizar");

                    return $this->render('FgimenezPlanillaBundle:Valoracionoac:evaluar.html.twig', array(
                                'valoracionOAC' => $valoracionOAC,
                                'form' => $editForm->createView(),
                                'delete_form' => $deleteForm->createView(),
                                'ente_organo' => $enteOrgano,
                                'competencia' => $compete,
                                'nombre_analista' => $nombre_analista,
                    ));
                }

                $valoracionOAC->setCorrecciones($correcciones);

                $nuevo_analista = $formulario['analista_asignado'];

                $valoracionOAC->getValoracionOACPlanilla()->setAnalistaAsignado($nuevo_analista);

                $obj_analista = $em->getRepository('dmanriqueUsuarioBundle:User')->findOneByUsername($nuevo_analista);

                $nombre_analista = $obj_analista->getNombre1() . ' ' . $obj_analista->getNombre2() . ' ' . $obj_analista->getApellido1() . ' ' . $obj_analista->getApellido2();

                $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(6);

                $mensaje = 'El caso ' . $valoracionOAC->getValoracionOACPlanilla()->getCodigo() . ' se ha enviado al analista ' . $nombre_analista . ' para su corrección';
            }
            if ($accion == 'evaluado') {

                $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(7);

                $mensaje = 'El caso ' . $valoracionOAC->getValoracionOACPlanilla()->getCodigo() . ' ha sido evaluado satisfactoriamente para su remisión';
            }



            $valoracionOAC->setUsuarioModificacion($this->getUser()->getUsername());

            $planilla = $valoracionOAC->getValoracionOACPlanilla();

            $planilla = $this->registrarEstatusPlanilla($planilla, $estatus);

            $planilla->setUsuarioModificacion($this->getUser()->getUsername());

            $em->persist($planilla);

            $em->persist($valoracionOAC);

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $mensaje);

            return $this->redirectToRoute('planilla_index_asignar');
        }

        return $this->render('FgimenezPlanillaBundle:Valoracionoac:evaluar.html.twig', array(
                    'valoracionOAC' => $valoracionOAC,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'ente_organo' => $enteOrgano,
                    'competencia' => $compete,
                    'nombre_analista' => $nombre_analista,
        ));
    }

    ///funcion que arma el pdf////////

    public function returnPDFResponseFromHTML($html, $origen) {

        $pdf = $this->container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetAuthor('User');
        $pdf->SetTitle(($origen));
        $pdf->SetSubject('Our');
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 11, '', true);
        $pdf->SetMargins(20, 20, 20, true);
        $pdf->SetHeaderMargin(0);

        $pdf->SetFooterData(array(255, 255, 255), array(255, 255, 255));
        $pdf->SetLineStyle(Array('9', 'butt', 'miter', 0, array(255, 255, 255)));

        $pdf->SetHeaderFont(Array(PDF_FONT_NAME_MAIN, '13', PDF_FONT_SIZE_MAIN));
        $pdf->SetFooterFont(Array(PDF_FONT_NAME_DATA, '13', PDF_FONT_SIZE_DATA));
        $pdf->SetPrintFooter(true);
        $pdf->SetPrintHeader(false);
        $pdf->AddPage();
        $filename = $origen;
        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'center', $autopadding = true);
        $pdf->Output($filename . ".pdf", 'I'); // This will output the PDF as a response directly
    }

    public function planillaValoracionAction(Planilla $planilla) {

        ob_clean();

        /* $usuario = $this->getDoctrine()
          ->getRepository('dmanriqueUsuarioBundle:User')
          ->findOneByUsername($solicitantes->getUsuarioCreacion()); */

        $em = $this->getDoctrine()->getManager();

        $id_compete = $planilla->getPlanillaValoracionOAC()->getCompetencia();

        if ($id_compete == 1) {

            $cgr = 'SI';

            $competencia = $em->getRepository('OrganizacionesBundle:organizaciones')->findOneById($planilla->getPlanillaValoracionOAC()->getOrganismoCompetencia());

            $compete = 'Contraloría General de la República - ' . $competencia->getNombre();
        } else {

            $cgr = 'NO';

            $competencia = $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($planilla->getPlanillaValoracionOAC()->getOrganismoCompetencia());

            $compete = 'Ente Externo - ' . $competencia->getDesEnte();
        }

        $id_ente = $planilla->getPlanillaValoracionOAC()->getEnteOrgano();

        if ($id_ente) {

            $ente_presentado = $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($id_ente);
            $ente = $ente_presentado->getDesEnte();
        } else {

            $ente = "";
        }

        $reporsitory = $em->getRepository("FgimenezPlanillaBundle:ValoracionOAC");


        $analista_atendido = $reporsitory->analistaPDF($planilla->getId(), 2);

        $analista_revisado = $reporsitory->analistaPDF($planilla->getId(), 7);


        $html = $this->renderView('FgimenezPlanillaBundle:Valoracionoac:planillaValoracion.html.twig', array(
            'planilla' => $planilla,
            'competencia' => $compete,
            'pertenece' => $cgr,
            'ente_presentado' => $ente,
            'atendido' => $analista_atendido['nombre_analista'],
            'revisado' => $analista_revisado['nombre_analista'],
        ));

        $origen = "Planilla Valoracion";

        /* return $this->render('FgimenezPlanillaBundle:Valoracionoac:planillaValoracion.html.twig', array(
          'planilla' =>$planilla,
          'competencia'=>$compete,
          'pertenece'=>$cgr,
          'ente_presentado'=>$ente_presentado->getDesEnte(),

          )); */

        $this->returnPDFResponseFromHTML($html, $origen);
    }

    //FUNCIONES DE REMITIR CASO




}
