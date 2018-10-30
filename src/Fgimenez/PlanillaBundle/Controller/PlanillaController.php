<?php

namespace Fgimenez\PlanillaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;
use Fgimenez\PlanillaBundle\Entity\Estatus;
use Fgimenez\PlanillaBundle\Entity\Planilla;
use Fgimenez\PlanillaBundle\Entity\EstatusPlanilla;
use Mmartin4\MantenimientoBundle\Entity\Mecanismo_Presentacion;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use dmanrique\UsuarioBundle\Entity\User;
use Symfony\Component\Form\FormView;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Mmartin4\MantenimientoBundle\Entity\motivo_reasignacion;
use Fgimenez\PlanillaBundle\Entity\ValoracionOAC;
use Fgimenez\PlanillaBundle\Entity\NotasRemisionDefinitivas;

/**
 * Planilla controller.
 *
 */
class PlanillaController extends Controller {

    /**
     * Lists all Planilla entities.
     *
     */
    public function indexAction(Request $request) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_LISTADOCASO')) {

            throw $this->createAccessDeniedException();
        }


        $em = $this->getDoctrine()->getManager();


       //
        $queryBuilder = $em->getRepository('FgimenezPlanillaBundle:Planilla')
                ->createQueryBuilder('e')
                ->where("(e.ultimo_status in (1,3,4,5,6,7,8) and e.AnalistaAsignado=:analista)")
                ->setParameter("analista", $this->getUser()->getUsername());
        //->orderBy('e.fecha_ultimo_estatus', 'DESC');


     // ???
        $url = 'planilla';

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);

        list($planillas, $pagerHtml, $existe) = $this->paginator($queryBuilder, $request, $url);

        if ($existe == 0) {

            $excedido = "";
        }

        foreach ($planillas as $planilla) {

            //se hace uso de la funcion fechaHabil para identificar
            //que un analista ya tiene este tiempo con un caso iniciado,
            //la funcion modifica el valor de $planilla
            //por lo que se debe setear nuevamente la fecha de ultimo estatus con el valor
            //de la posicion 0 del array fecha devuelto por la funcion


        //??
            $query = $em->getRepository('FgimenezPlanillaBundle:EstatusPlanilla')
                    ->createQueryBuilder('s')
                    ->select('MIN(s.created) AS created')
                    ->where('s.id_documento=:planilla and s.id_estatus=:estatus and s.AnalistaAsignado=:analista')
                    ->setParameter("planilla", $planilla->getId())
                    ->setParameter("estatus", $planilla->getUltimoStatus()->getId())
                    ->setParameter("analista", $planilla->getAnalistaAsignado());


            $resultado = $query->getQuery()->getResult();

            //$fecha_estatus = $resultado[0]['created'];

            $fecha_estatus = new \DateTime($resultado[0]['created']);

            // }else{
            //$fecha_estatus=$planilla->getfechaUltimoStatus();
            //}

            $dias = 2;

            $fecha = $this->fechaHabil($fecha_estatus, $dias);

            //$fecha = $this->fechaHabil($planilla->getfechaUltimoStatus(), $dias);

            $planilla->setfechaUltimoStatus($fecha[0]);

            $fecha_maxima = strtotime($fecha[$dias]);

            $fecha_actual = strtotime(date('Y-m-d'));

            if ($fecha_maxima <= $fecha_actual) {

                $valor = 'si';
            } else {

                $valor = 'no';
            }

            $excedido[] = $valor;

            $analista = $em->getRepository('dmanriqueUsuarioBundle:User')->findOneByUsername($planilla->getAnalistaAsignado());

            if ($analista) {

                $nombre_analista = $analista->getNombre1() . ' ' . $analista->getNombre2() . ' ' . $analista->getApellido1() . ' ' . $analista->getApellido2();

                $planilla->setAnalistaAsignado(ucwords(strtolower($nombre_analista)));
            }
        }


        return $this->render('FgimenezPlanillaBundle:Planilla:index.html.twig', array(
                    'planillas' => $planillas,
                    'pagerHtml' => $pagerHtml,
                    'filterForm' => $filterForm->createView(),
                    'excedido' => $excedido,
        ));
    }

    /**
     * Create filter form and process filter request.
     *
     */
    protected function filter($queryBuilder, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();

        $filterForm = $this->createForm('Fgimenez\PlanillaBundle\Form\PlanillaFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('PlanillaControllerFilter');
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
                $session->set('PlanillaControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('PlanillaControllerFilter')) {
                $filterData = $session->get('PlanillaControllerFilter');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('Fgimenez\PlanillaBundle\Form\PlanillaFilterType', $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }

    /**
     * Get results from paginator and get paginator view.
     *
     */
    protected function paginator($queryBuilder, Request $request, $url) {


        //sorting
        //$sortCol = $queryBuilder->getRootAlias() . '.' . $request->get('pcg_sort_col', 'id');
        $sortCol = $queryBuilder->getRootAlias() . '.' . $request->get('pcg_sort_col', 'fechaUltimoStatus');
        $queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($request->get('pcg_show', 5));

        try {
            $pagerfanta->setCurrentPage($request->get('pcg_page', 1));
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
            $pagerfanta->setCurrentPage(1);
        }

        $entities = $pagerfanta->getCurrentPageResults();

        $existe = $pagerfanta->getNbResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me, $request, $url) {
            $requestParams = $request->query->all();
            $requestParams['pcg_page'] = $page;
            //return $me->generateUrl('planilla', $requestParams);
            return $me->generateUrl($url, $requestParams);
        };

        // Paginator - view
        $view = new TwitterBootstrap3View();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => 'anterior',
            'next_message' => 'siguiente',
        ));

        return array($entities, $pagerHtml, $existe);
    }

    /**
     * Displays a form to create a new Planilla entity.
     *
     */
    public function newAction(Request $request) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_NUEVOCASO')) {

            throw $this->createAccessDeniedException();
        }

        $planilla = new Planilla();

        $form = $this->createForm('Fgimenez\PlanillaBundle\Form\PlanillaType', $planilla);

        $form->handleRequest($request);

        return $this->render('FgimenezPlanillaBundle:Planilla:new.html.twig', array(
                    'planilla' => $planilla,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Función que guarda los datos en BD
     */
    public function guardarDatosAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $planilla = new Planilla();

        $planilla = $this->get('app.datosUsuario')->setUsuarioCreacion($planilla, $this->getUser()->getUsername());

        list($formulario, $tipo_caso, $editForm) = $this->obtenerFormulario($request, $planilla);

        list($estado, $municipio, $parroquia, $json) = $this->datosAdicionalesAction($request, $tipo_caso);

        $planilla->setEstado($estado);

        $planilla->setMunicipio($municipio);

        $planilla->setParroquia($parroquia);

        $planilla->setAdicionales($json);

        $planilla->setHecho($formulario['hecho']);

        $planilla->setFechaHecho(new \DateTime($formulario['fechaHecho']));

        $planilla->setMecanismoPresentacion($formulario['tipo_atencion']);

        $planilla->setAnalistaAsignado($this->getUser()->getUsername());

        $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(1);

        list($estatus_planilla, $planilla) = $this->registrarEstatusPlanilla($planilla, $estatus);

        $planilla->setIdTipo($tipo_caso);

        $obj_tipo_caso = $em->getRepository('FgimenezPlanillaBundle:TipoCaso')->findOneBy(array('id' => $tipo_caso));

        $planilla->setTipoCaso($obj_tipo_caso);

        $obj_mecanismo = $em->getRepository('Mmartin4MantenimientoBundle:Mecanismo_Presentacion')->findOneBy(array('id' => $formulario['tipo_atencion']));

        $planilla->setTipoAtencion($obj_mecanismo);

        $em->persist($planilla);

        $planilla->setCodigo($this->generarCodigo($planilla, $tipo_caso));

        $em->flush();

        $form = $this->createForm('Fgimenez\PlanillaBundle\Form\PlanillaType', $planilla);

        return $this->json(array(
                    'mensaje' => 'Caso ' . $planilla->getCodigo() . ' creado exitosamente!!!',
        ));
    }

    public function obtenerFormulario(Request $request, Planilla $planilla) {

        if ($planilla->getIdTipo() != "") {

            $tipo_caso = $planilla->getIdTipo();
        } else {

            $formulario1 = $request->get('planilla');
            $tipo_caso = $formulario1['tipo_caso'];
        }

        if ($tipo_caso == 1) {

            $formulario = $request->get('denuncia');
            $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\DenunciaType', $planilla);
        }
        if ($tipo_caso == 2) {

            $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\ReclamoType', $planilla);
            $formulario = $request->get('reclamo');
        }
        if ($tipo_caso == 3) {

            $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\QuejaType', $planilla);
            $formulario = $request->get('queja');
        }
        if ($tipo_caso == 4) {
            $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\PeticionType', $planilla);
            $formulario = $request->get('peticion');
        }
        if ($tipo_caso == 5) {
            $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\SugerenciaType', $planilla);
            $formulario = $request->get('sugerencia');
        }

        return array($formulario, $tipo_caso, $editForm);
    }

    /**
     * Funcion que manipula los datos para guardar direccion
     */
    public function datosAdicionalesAction(Request $request, $tipo_caso) {

        list($estado, $municipio, $parroquia, $json) = $this->crearJsonDireccionAction($request, $tipo_caso);

        if ($tipo_caso == 2) {

            $json = $this->crearJsonReclamoAction($request, $json);
        }

        if ($tipo_caso == 3) {

            $json = $this->crearJsonQuejaAction($request, $json);
        }

        if ($tipo_caso == 4) {

            $json = $this->crearJsonPeticionAction($request, $json);
        }

        if ($tipo_caso == 5) {

            $json = $this->crearJsonSugerenciaAction($request, $json);
        }

        return array($estado, $municipio, $parroquia, $json);
    }

    public function crearJsonDireccionAction($request, $tipo_caso) {

        $em = $this->getDoctrine()->getManager();

        $estado = $request->get('Estado');

        $municipio = $request->get('municipio');

        $parroquia = $request->get('parroquia') + 1;

        $parametro_edo = array('idEdo' => $estado);

        $parametro_mun = array('idEdo' => $estado, 'idMuni' => $municipio);

        $parametro_paq = array('idEdo' => $estado, 'idMuni' => $municipio, 'idParro' => $parroquia);

        $obj_estado = $em->getRepository('territorioBundle:estados')->findOneBy($parametro_edo);

        $obj_municipio = $em->getRepository('territorioBundle:municipio')->findOneBy($parametro_mun);

        $obj_parroquia = $em->getRepository('territorioBundle:parroquias')->findOneBy($parametro_paq);

        $des_estado = $obj_estado->getDesEdo();

        $des_municipio = $obj_municipio->getDesMuni();

        $des_parroquia = $obj_parroquia->getDesParro();

        $json = $this->get('app.datos_direccion')->DireccionCorta($des_estado, $des_municipio, $des_parroquia);

        return array($estado, $municipio, $parroquia, $json);
    }

    public function crearJsonQuejaAction($request, $json) {


        $formulario = $request->get('queja');

        $json['id_naturaleza'] = $formulario['naturaleza'];

        $json['id_condicion'] = $formulario['condicion'];

        return $json;
    }

    public function crearJsonReclamoAction($request, $json) {

        $formulario = $request->get('reclamo');

        $json['id_naturaleza'] = $formulario['naturaleza'];

        $json['id_condicion'] = $formulario['condicion'];

        return $json;
    }

    public function crearJsonSugerenciaAction($request, $json) {

        //$em = $this->getDoctrine()->getManager();

        $formulario = $request->get('sugerencia');

        //$parametro_naturaleza = array('id' => $formulario['naturaleza']);
        //$obj_naturaleza = $em->getRepository('FgimenezPlanillaBundle:Naturaleza')->findOneBy($parametro_naturaleza);

        $json['id_naturaleza'] = $formulario['naturaleza'];

        //$json['naturaleza'] = $obj_naturaleza->getNombre();

        $json['sustento'] = $formulario['sustento'];

        return $json;
    }

    public function crearJsonPeticionAction($request, $json) {

        $formulario = $request->get('peticion');

        $json['solicitud'] = $formulario['solicitud'];

        return $json;
    }

    /**
     * Cambia el Estatus
     *
     */
    public function cambiarEstatusAction(Request $request, Planilla $planilla) {


        $estatus_planilla = new EstatusPlanilla();

        $em = $this->getDoctrine()->getManager();

        $this->estatus_doc = $request->get('documento_estatus');

        $this->funcion_validacion_db = $request->get('repository_verificar_cambio_estatus');


        $repository_planilla = $em->getRepository('FgimenezPlanillaBundle:Planilla');

        $validar_fase = $this->validarFaseI($planilla);

        $repository_estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus');

        $estatus = $repository_estatus->findOneById($this->estatus_doc);


        if ($validar_fase === true) {

            list($estatus_planilla, $planilla) = $this->registrarEstatusPlanilla($planilla, $estatus);

            $em->persist($planilla);

            $em->flush();

            $editLink = $this->generateUrl('reporte_planilla', array('id' => $planilla->getId()));

            $pdfPlanilla = '<script>var strWindowFeatures = "location=yes,height=570,width=520,scrollbars=yes,status=yes"; window.open("' . 'http://' . $_SERVER['SERVER_NAME'] . $editLink . '", "_blank", null);</script>';

            $this->get('session')->getFlashBag()->add('success', 'Caso ' . $planilla->getCodigo() . ' Cambiado a Estatus ' . $estatus->getNombre() . '. ' . $pdfPlanilla);
        } else {

            //$this->get('session')->getFlashBag()->add('error', 'No cumple con los requisitos para cambiar a ' . $estatus->getNombre());

            return $this->redirectToRoute('planilla_edit', array(
                        'id' => $planilla->getId()
            ));
        }

        return $this->redirectToRoute('planilla');
    }

    private function validarFaseI(Planilla $planilla) {

        $hay_involucrados = false;

        $hay_solicitantes = false;

        $hay_soportes = false;

        $involucrados = $planilla->getPlanillaInvolucrado();

        $valido = true;

        foreach ($involucrados as $involucrado) {
            $hay_involucrados = true;
        }

        if (!$hay_involucrados) {
            $this->get('session')->getFlashBag()->add('error', 'No puede pasar a la siguiente fase ya que no hay involucrados asociados');
            $valido = false;
        }


        if ($planilla->getIdTipo() == 1 || $planilla->getIdTipo() == 2) {

            $soportes = $planilla->getDocumentos();

            foreach ($soportes as $soporte) {
                $hay_soportes = true;
            }

            if (!$hay_soportes) {
                $this->get('session')->getFlashBag()->add('error', 'No puede pasar a la siguiente fase ya que no hay soportes documentales asociados');
                $valido = false;
            }
        }



        $solicitantes = $planilla->getSolicitantes();

        foreach ($solicitantes as $solicitante) {
            $hay_solicitantes = true;
        }
        if (!$hay_solicitantes) {
            $this->get('session')->getFlashBag()->add('error', 'No puede pasar a la siguiente fase ya que no hay requirientes asociados ');
            $valido = false;
        }


        return $valido;
    }

    /**
     * Finds and displays a Planilla entity.
     *
     */
    public function showAction(Request $request, Planilla $planilla) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_VERCASO')) {

            throw $this->createAccessDeniedException();
        }

        $origen = $request->get('origen');

        $deleteForm = $this->createDeleteForm($planilla);
        $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\PlanillaType', $planilla);
        $editForm->handleRequest($request);

        return $this->render('FgimenezPlanillaBundle:Planilla:show.html.twig', array(
                    'planilla' => $planilla,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'origen' => $origen,
        ));
    }

    public function showCasoAction(Planilla $planilla) {

        $tipo_caso = $planilla->getIdTipo();

        $em = $this->getDoctrine()->getManager();

        $adicionales = $planilla->getAdicionales();

        $usuarioCreacion = $em->getRepository('dmanriqueUsuarioBundle:User')
                ->findOneByUsername($planilla->getUsuarioCreacion());


        $usuarioModificacion = $em->getRepository('dmanriqueUsuarioBundle:User')
                ->findOneByUsername($planilla->getUsuarioModificacion());


        if ($tipo_caso == 1) {

            $vista = 'FgimenezPlanillaBundle:Denuncia:show_denuncia.html.twig';
        }
        if ($tipo_caso == 2) {

            $parametro_naturaleza = array('id' => $adicionales['id_naturaleza']);

            $parametro_condicion = array('id' => $adicionales['id_condicion']);

            $obj_naturaleza = $em->getRepository('FgimenezPlanillaBundle:Naturaleza')->findOneBy($parametro_naturaleza);

            $obj_condicion = $em->getRepository('FgimenezPlanillaBundle:Condicion')->findOneBy($parametro_condicion);

            $planilla->setNaturaleza($obj_naturaleza);

            $planilla->setCondicion($obj_condicion);

            $vista = 'FgimenezPlanillaBundle:Reclamo:show_reclamo.html.twig';
        }
        if ($tipo_caso == 3) {

            $parametro_naturaleza = array('id' => $adicionales['id_naturaleza']);

            $parametro_condicion = array('id' => $adicionales['id_condicion']);

            $obj_naturaleza = $em->getRepository('FgimenezPlanillaBundle:Naturaleza')->findOneBy($parametro_naturaleza);

            $obj_condicion = $em->getRepository('FgimenezPlanillaBundle:Condicion')->findOneBy($parametro_condicion);

            $planilla->setNaturaleza($obj_naturaleza);

            $planilla->setCondicion($obj_condicion);

            $vista = 'FgimenezPlanillaBundle:Queja:show_queja.html.twig';
        }
        if ($tipo_caso == 4) {


            $vista = 'FgimenezPlanillaBundle:Peticion:show_peticion.html.twig';
        }
        if ($tipo_caso == 5) {


            $parametro_naturaleza = array('id' => $adicionales['id_naturaleza']);

            $obj_naturaleza = $em->getRepository('FgimenezPlanillaBundle:Naturaleza')->findOneBy($parametro_naturaleza);

            $planilla->setNaturaleza($obj_naturaleza);

            $vista = 'FgimenezPlanillaBundle:Sugerencia:show_sugerencia.html.twig';
        }

        return $this->render($vista, array(
                    'planilla' => $planilla,
                    'usuarioCreacion' => $usuarioCreacion,
                    'usuarioModificacion' => $usuarioModificacion,
        ));
    }

    /**
     * Displays a form to edit an existing Planilla entity.
     *
     */
    public function editAction(Request $request, Planilla $planilla) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_EDITARCASO')) {

            throw $this->createAccessDeniedException();
        }

        return $this->render('FgimenezPlanillaBundle:Planilla:edit.html.twig', array(
                    'planilla' => $planilla,
        ));
    }

    public function agregarSoportesAction(Request $request, Planilla $planilla) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_AGREGARSOPORTES')) {

            throw $this->createAccessDeniedException();
        }

        return $this->render('FgimenezPlanillaBundle:Planilla:edit_soporte.html.twig', array(
                    'planilla' => $planilla,
                    'origen' => $request->get('origen'),
        ));
    }

    public function mostrarEditarAction(Request $request) {


        $planilla = $request->get('valor');

        $tipo_caso = $planilla->getIdTipo();

        if ($tipo_caso == 1) {

            return $this->mostrarEditDenunciaAction($request);
        }
        if ($tipo_caso == 2) {

            return $this->mostrarEditReclamoAction($request);
        }

        if ($tipo_caso == 3) {

            return $this->mostrarEditQuejaAction($request);
        }
        if ($tipo_caso == 4) {

            return $this->mostrarEditPeticionAction($request);
        }
        if ($tipo_caso == 5) {

            return $this->mostrarEditSugerenciaAction($request);
        }
    }

    public function mostrarEditDenunciaAction($request) {

        $planilla = $request->get('valor');

        $deleteForm = $this->createDeleteForm($planilla);

        $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\DenunciaType', $planilla);

        $editForm->handleRequest($request);

        $vista = 'FgimenezPlanillaBundle:Denuncia:edit_denuncia.html.twig';

        return $this->render($vista, array(
                    'planilla' => $planilla,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    public function mostrarEditQuejaAction($request) {

        $em = $this->getDoctrine()->getManager();

        $planilla = $request->get('valor');

        $adicionales = $planilla->getAdicionales();

        $parametro_naturaleza = array('id' => $adicionales['id_naturaleza']);

        $parametro_condicion = array('id' => $adicionales['id_condicion']);

        $obj_naturaleza = $em->getRepository('FgimenezPlanillaBundle:Naturaleza')->findOneBy($parametro_naturaleza);

        $obj_condicion = $em->getRepository('FgimenezPlanillaBundle:Condicion')->findOneBy($parametro_condicion);

        $planilla->setNaturaleza($obj_naturaleza);

        $planilla->setCondicion($obj_condicion);

        $deleteForm = $this->createDeleteForm($planilla);

        $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\QuejaType', $planilla);

        $editForm->handleRequest($request);

        $vista = 'FgimenezPlanillaBundle:Queja:edit_queja.html.twig';

        return $this->render($vista, array(
                    'planilla' => $planilla,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    public function mostrarEditReclamoAction($request) {

        $em = $this->getDoctrine()->getManager();

        $planilla = $request->get('valor');

        $adicionales = $planilla->getAdicionales();

        $parametro_naturaleza = array('id' => $adicionales['id_naturaleza']);

        $parametro_condicion = array('id' => $adicionales['id_condicion']);

        $obj_naturaleza = $em->getRepository('FgimenezPlanillaBundle:Naturaleza')->findOneBy($parametro_naturaleza);

        $obj_condicion = $em->getRepository('FgimenezPlanillaBundle:Condicion')->findOneBy($parametro_condicion);

        $planilla->setNaturaleza($obj_naturaleza);

        $planilla->setCondicion($obj_condicion);

        $deleteForm = $this->createDeleteForm($planilla);

        $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\ReclamoType', $planilla);

        $editForm->handleRequest($request);

        $vista = 'FgimenezPlanillaBundle:Reclamo:edit_reclamo.html.twig';

        return $this->render($vista, array(
                    'planilla' => $planilla,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    public function mostrarEditSugerenciaAction($request) {

        $em = $this->getDoctrine()->getManager();

        $planilla = $request->get('valor');

        $adicionales = $planilla->getAdicionales();

        $parametro_naturaleza = array('id' => $adicionales['id_naturaleza']);

        $obj_naturaleza = $em->getRepository('FgimenezPlanillaBundle:Naturaleza')->findOneBy($parametro_naturaleza);

        $planilla->setNaturaleza($obj_naturaleza);

        $planilla->setSustento($adicionales['sustento']);

        $deleteForm = $this->createDeleteForm($planilla);

        $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\SugerenciaType', $planilla);

        $editForm->handleRequest($request);

        $vista = 'FgimenezPlanillaBundle:Sugerencia:edit_sugerencia.html.twig';

        return $this->render($vista, array(
                    'planilla' => $planilla,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    public function mostrarEditPeticionAction($request) {

        $em = $this->getDoctrine()->getManager();

        $planilla = $request->get('valor');

        $adicionales = $planilla->getAdicionales();

        $planilla->setSolicitud($adicionales['solicitud']);

        $deleteForm = $this->createDeleteForm($planilla);

        $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\PeticionType', $planilla);

        $editForm->handleRequest($request);

        $vista = 'FgimenezPlanillaBundle:Peticion:edit_peticion.html.twig';

        return $this->render($vista, array(
                    'planilla' => $planilla,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    public function guardarEditarAction(Request $request, Planilla $planilla) {

        $em = $this->getDoctrine()->getManager();

        $deleteForm = $this->createDeleteForm($planilla);

        $planilla = $this->get('app.datosUsuario')->setUsuarioModificacion($planilla, $this->getUser()->getUsername());

        list($formulario, $tipo_caso, $editForm) = $this->obtenerFormulario($request, $planilla);

        $editForm->handleRequest($request);

        list($estado, $municipio, $parroquia, $json) = $this->datosAdicionalesAction($request, $planilla->getIdTipo());

        $planilla->setEstado($estado);

        $planilla->setMunicipio($municipio);

        $planilla->setParroquia($parroquia);

        $planilla->setAdicionales($json);

        $em->persist($planilla);

        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Caso ' . $planilla->getCodigo() . ' Editado Exitosamente!');

        return $this->redirectToRoute('planilla');
    }

    /**
     * Deletes a Planilla entity.
     *
     */
    public function deleteAction(Request $request, Planilla $planilla) {

        $form = $this->createDeleteForm($planilla);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($planilla);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Documento fue eliminado exitosamente');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con Planilla');
        }

//        return $this->redirectToRoute('planilla');
    }

    /**
     * Creates a form to delete a Planilla entity.
     *
     * @param Planilla $planilla The Planilla entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Planilla $planilla) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('planilla_delete', array('id' => $planilla->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * Delete Planilla by id
     *
     */
    public function deleteByIdAction(Planilla $planilla) {
        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($planilla);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Documento fue eliminado exitosamente');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con Planilla');
        }

        return $this->redirect($this->generateUrl('planilla'));
    }

    /**
     * Bulk Action esta funcion se usa para asignar un caso a varios analistas
     */
    public function bulkAction(Request $request) {

        $ids = $request->get("ids", array());

        $estatus_planilla = new EstatusPlanilla();

        $analista = $request->get("analista");

        if ($analista == "0" || empty($ids)) {

            $this->get('session')->getFlashBag()->add('error', 'Debe seleccionar al menos un caso e indicar el nombre del analista');

            return $this->redirect($this->generateUrl('planilla_index_asignar'));
        }

        $em = $this->getDoctrine()->getManager();

        $analista = $em->getRepository('dmanriqueUsuarioBundle:User')->findOneByUsername($analista);

        $nombre_analista = $analista->getNombre1() . ' ' . $analista->getNombre2() . ' ' . $analista->getApellido1() . ' ' . $analista->getApellido2();

        $repository = $em->getRepository('FgimenezPlanillaBundle:Planilla');

        $casos = '';

        foreach ($ids as $id) {



            $planilla = $repository->find($id);

            $planilla->setAnalistaAsignado($analista);

            $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(3);

            list($estatus_planilla, $planilla) = $this->registrarEstatusPlanilla($planilla, $estatus);

            $planilla->setUsuarioModificacion($this->getUser()->getUsername());

            $casos = $planilla->getCodigo() . ', ' . $casos;

            $em->persist($planilla);

            $em->flush();

            echo $planilla->getCodigo();
        }


        $this->get('session')->getFlashBag()->add('success', 'Los casos ' . $casos . ' han sido asignados al analista ' . ucwords(strtolower($nombre_analista)));


        return $this->redirect($this->generateUrl('planilla_index_asignar'));
    }

    public function generarCodigo(Planilla $planilla, $tipo_caso) {


        $em = $this->getDoctrine()->getEntityManager();
        //De esta manera se realiza en llamado de una funcion en el repositoty(donde estan todos los métodos que realizan operaciones a la base de datos)
        $funcion = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        $correlativo = $funcion->consultar_correlativo($planilla->getTipoCaso()->getId());

        // return 'doc' . '-' . date('Y') . '-' . $planilla->getId();

        if ($tipo_caso == 1) {
            return 'D' . '-' . date('Y') . '-' . $correlativo['correlativo'];
        }

        if ($tipo_caso == 2) {
            return 'R' . '-' . date('Y') . '-' . $correlativo['correlativo'];
        }
        if ($tipo_caso == 3) {
            return 'Q' . '-' . date('Y') . '-' . $correlativo['correlativo'];
        }
        if ($tipo_caso == 4) {
            return 'P' . '-' . date('Y') . '-' . $correlativo['correlativo'];
        }
        if ($tipo_caso == 5) {
            return 'S' . '-' . date('Y') . '-' . $correlativo['correlativo'];
        }
    }

    public function mostrarFormularioAction(Request $request) {

        $tipo_requerimiento = $request->request->get("tipo_requerimiento");
        $planilla = new Planilla();

        if ($tipo_requerimiento == 1) {
            $form = $this->createForm('Fgimenez\PlanillaBundle\Form\DenunciaType', $planilla);
            $vista = 'FgimenezPlanillaBundle:Denuncia:new_denuncia.html.twig';
        }
        if ($tipo_requerimiento == 2) {

            $form = $this->createForm('Fgimenez\PlanillaBundle\Form\ReclamoType', $planilla);
            $vista = 'FgimenezPlanillaBundle:Reclamo:new_reclamo.html.twig';
        }
        if ($tipo_requerimiento == 3) {

            $form = $this->createForm('Fgimenez\PlanillaBundle\Form\QuejaType', $planilla);
            $vista = 'FgimenezPlanillaBundle:Queja:new_queja.html.twig';
        }
        if ($tipo_requerimiento == 4) {

            $form = $this->createForm('Fgimenez\PlanillaBundle\Form\PeticionType', $planilla);
            $vista = 'FgimenezPlanillaBundle:Peticion:new_peticion.html.twig';
        }
        if ($tipo_requerimiento == 5) {

            $form = $this->createForm('Fgimenez\PlanillaBundle\Form\SugerenciaType', $planilla);
            $vista = 'FgimenezPlanillaBundle:Sugerencia:new_sugerencia.html.twig';
        }

        return $this->render($vista, array(
                    'form' => $form->createView(),
                    'planilla' => $planilla));
    }

    /*     * *FUNCIONES DE LA OPCION ASIGNAR CASO**** */

    public function indexAsignarAction(Request $request) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_LISTADOASIGNAR')) {

            throw $this->createAccessDeniedException();
        }



        $em = $this->getDoctrine()->getManager();

        /* $queryBuilder = $em->getRepository('FgimenezPlanillaBundle:Planilla')
          ->createQueryBuilder('e')
          ->where("e.ultimo_status in (2,3,4)")
          ->orderBy('e.ultimo_estatus', 'ASC'); */

        $estatus = array(2, 3, 4, 5, 6, 7, 8);

        $queryBuilder = $em->getRepository('FgimenezPlanillaBundle:Planilla')
                ->createQueryBuilder('e')
                ->where("e.ultimo_status in (:estatus)")
                ->setParameter('estatus', $estatus)
                ->orderBy('e.fecha_ultimo_estatus', 'DESC');


        $url = 'planilla_index_asignar';

        //$excedido[] = "";

        list($filterForm, $queryBuilder) = $this->filterAsignar($queryBuilder, $request);

        list($planillas, $pagerHtml, $existe) = $this->paginator($queryBuilder, $request, $url);

        $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        list($combo_analista1, $combo_analista2) = $planilla_repo->comboAnalista();

        if ($existe == 0) {

            $excedido = "";
        }

        foreach ($planillas as $planilla) {

            //se hace uso de la funcion fechaHabil para identificar
            //que un analista ya tiene este tiempo con un caso iniciado,
            //la funcion modifica el valor de $planilla
            //por lo que se debe setear nuevamente la fecha de ultimo estatus con el valor
            //de la posicion 0 del array fecha devuelto por la funcion



            $query = $em->getRepository('FgimenezPlanillaBundle:EstatusPlanilla')
                    ->createQueryBuilder('s')
                    ->select('MIN(s.created) AS created')
                    ->where('s.id_documento=:planilla and s.id_estatus=:estatus and s.AnalistaAsignado=:analista')
                    ->setParameter("planilla", $planilla->getId())
                    ->setParameter("estatus", $planilla->getUltimoStatus()->getId())
                    ->setParameter("analista", $planilla->getAnalistaAsignado());


            $resultado = $query->getQuery()->getResult();

            //$fecha_estatus = $resultado[0]['created'];

            $fecha_estatus = new \DateTime($resultado[0]['created']);

            $dias = 2;

            $fecha = $this->fechaHabil($fecha_estatus, $dias);

            $planilla->setfechaUltimoStatus($fecha[0]);

            $fecha_maxima = strtotime($fecha[$dias]);

            $fecha_actual = strtotime(date('Y-m-d'));

            if ($fecha_maxima <= $fecha_actual) {

                $valor = 'si';
            } else {

                $valor = 'no';
            }

            $excedido[] = $valor;

            $analista = $em->getRepository('dmanriqueUsuarioBundle:User')->findOneByUsername($planilla->getAnalistaAsignado());

            if ($analista) {

                $nombre_analista = $analista->getNombre1() . ' ' . $analista->getNombre2() . ' ' . $analista->getApellido1() . ' ' . $analista->getApellido2();

                $planilla->setAnalistaAsignado(ucwords(strtolower($nombre_analista)));
            }
        }




        return $this->render('FgimenezPlanillaBundle:Asignar:index.html.twig', array(
                    'planillas' => $planillas,
                    'pagerHtml' => $pagerHtml,
                    'filterForm' => $filterForm->createView(),
                    'excedido' => $excedido,
                    'analistas' => $combo_analista2,
                        //'nombre_analista'=>$nombre,
        ));
    }

    public function AsignarCasoAction(Request $request, Planilla $planilla) {


        $origen = $request->get('origen');

        if ($origen == 'asignar') {

            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ASIGNARCASO')) {

                throw $this->createAccessDeniedException();
            }
        }
        if ($origen == 'reasignar') {

            if (!$this->get('security.authorization_checker')->isGranted('ROLE_REASIGNARCASO')) {

                throw $this->createAccessDeniedException();
            }
        }

        $estatus_planilla = new EstatusPlanilla();

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('Fgimenez\PlanillaBundle\Form\AsignarType', $planilla);

        $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        list($comboAnalista, $combo_analista2) = $planilla_repo->comboAnalista();

        //variable auxiliar que mantiene el analista asignado
        //para poder setearle a planilla analista en vacio y se muestre
        //el combo en seleccione

        $analista_aux = $planilla->getAnalistaAsignado();

        $planilla->setAnalistaAsignado("");

        $form->add('analista_asignado', ChoiceType::class, array(
            'label' => 'Analista',
            'required' => true,
            'choices' => $comboAnalista,
        ));

        $nombre_analista = "";

        $obj_analista = $em->getRepository('dmanriqueUsuarioBundle:User')->findOneByUsername($analista_aux);

        $nombre_analista = $obj_analista->getNombre1() . ' ' . $obj_analista->getNombre2() . ' ' . $obj_analista->getApellido1() . ' ' . $obj_analista->getApellido2();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $em = $this->getDoctrine()->getManager();

            $obj_analista = $em->getRepository('dmanriqueUsuarioBundle:User')->findOneByUsername($planilla->getAnalistaAsignado());

            $nombre_analista = $obj_analista->getNombre1() . ' ' . $obj_analista->getNombre2() . ' ' . $obj_analista->getApellido1() . ' ' . $obj_analista->getApellido2();

            $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(3);

            list($estatus_planilla, $planilla) = $this->registrarEstatusPlanilla($planilla, $estatus);

            $planilla->setUsuarioModificacion($this->getUser()->getUsername());

            $em->persist($planilla);

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'El caso ' . $planilla->getCodigo() . ' ha sido asignado al analista ' . ucwords(strtolower($nombre_analista)));

            return $this->redirectToRoute('planilla_index_asignar');
        }

        return $this->render('FgimenezPlanillaBundle:Asignar:new.html.twig', array(
                    'planilla' => $planilla,
                    'analista_actual' => $nombre_analista,
                    'form' => $form->createView(),
        ));
    }

    public function ReversarCasoAction(Request $request, Planilla $planilla) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_REVERSAR')) {

            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('Fgimenez\PlanillaBundle\Form\ReversarType', $planilla);

        $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        list($comboAnalista, $combo_analista2) = $planilla_repo->comboAnalistaReverso();

        //variable auxiliar que mantiene el analista asignado
        //para poder setearle a planilla analista en vacio y se muestre
        //el combo en seleccione

        $analista_aux = $planilla->getAnalistaAsignado();

        $planilla->setAnalistaAsignado("");

        $form->add('analista_asignado', ChoiceType::class, array(
            'label' => 'Analista',
            'required' => true,
            'choices' => $comboAnalista,
        ));

        $nombre_analista = "";

        $obj_analista = $em->getRepository('dmanriqueUsuarioBundle:User')->findOneByUsername($analista_aux);

        $nombre_analista = $obj_analista->getNombre1() . ' ' . $obj_analista->getNombre2() . ' ' . $obj_analista->getApellido1() . ' ' . $obj_analista->getApellido2();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $em = $this->getDoctrine()->getManager();

            $obj_analista = $em->getRepository('dmanriqueUsuarioBundle:User')->findOneByUsername($planilla->getAnalistaAsignado());

            $nombre_analista = $obj_analista->getNombre1() . ' ' . $obj_analista->getNombre2() . ' ' . $obj_analista->getApellido1() . ' ' . $obj_analista->getApellido2();

            $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(1);

            list($estatus_planilla, $planilla) = $this->registrarEstatusPlanilla($planilla, $estatus);

            $planilla->setUsuarioModificacion($this->getUser()->getUsername());

            $em->persist($planilla);

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'El caso ' . $planilla->getCodigo() . ' ha sido reversado al analista ' . ucwords(strtolower($nombre_analista)));



            return $this->redirectToRoute('planilla_index_asignar');
        }

        return $this->render('FgimenezPlanillaBundle:Reversar:new.html.twig', array(
                    'planilla' => $planilla,
                    'analista_actual' => $nombre_analista,
                    'form' => $form->createView(),
        ));
    }

    protected function filterAsignar($queryBuilder, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();

        $filterForm = $this->createForm('Fgimenez\PlanillaBundle\Form\AsignarFilterType');

        $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        list($comboAnalista, $combo_analista2) = $planilla_repo->comboAnalista();

        $filterForm->add('AnalistaAsignado', Filters\ChoiceFilterType::class, array(
            'label' => 'Analista',
            'required' => false,
            'choices' => $comboAnalista,
        ));



        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('PlanillaControllerFilterAsignar');
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
                $session->set('PlanillaControllerFilterAsignar', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('PlanillaControllerFilterAsignar')) {
                $filterData = $session->get('PlanillaControllerFilterAsignar');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('Fgimenez\PlanillaBundle\Form\AsignarFilterType', $filterData);

                /*                 * *********************************************************************** */
                /* cada vez que se cree el formulario de filter form se debe crear el campo analista*** */

                $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

                list($comboAnalista, $combo_analista2) = $planilla_repo->comboAnalista();

                $filterForm->add('AnalistaAsignado', Filters\ChoiceFilterType::class, array(
                    'label' => 'Analista',
                    'required' => false,
                    'choices' => $comboAnalista,
                ));
                /*                 * ***************************************** */

                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }

    public function fechaHabil($from, $days) {


        $days = 2;

        $em = $this->getDoctrine()->getEntityManager();

        $funcion = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        $holidayDays = $funcion->consultar_dias_feriados();

        $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Lunes, ...)

        $dates = [];
        $dates[] = $from->format('Y-m-d H:m:s');


        while ($days) {

            $from->modify('+1 day');

            if (!in_array($from->format('N'), $workingDays))
                continue;
            if (in_array($from->format('Y-m-d'), $holidayDays))
                continue;
            if (in_array($from->format('*-m-d'), $holidayDays))
                continue;

            $dates[] = $from->format('Y-m-d H:m:s');
            $days--;
        }

        return $dates;
    }

    public function iniciarValoracionAction(Planilla $planilla) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_INICIARVALORACION')) {

            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(4);

        list($estatus_planilla, $planilla) = $this->registrarEstatusPlanilla($planilla, $estatus);

        $planilla->setUsuarioModificacion($this->getUser()->getUsername());

        $em->persist($planilla);

        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'El caso ' . $planilla->getCodigo() . ' ha sido iniciado');

        return $this->redirectToRoute('planilla');
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

        return array($estatus_planilla, $planilla);
    }

    public function indexRemitirAction(Request $request) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_LISTADOREMITIR')) {

            throw $this->createAccessDeniedException();
        }



        $em = $this->getDoctrine()->getManager();

        /* $queryBuilder = $em->getRepository('FgimenezPlanillaBundle:Planilla')
          ->createQueryBuilder('e')
          ->where("e.ultimo_status in (2,3,4)")
          ->orderBy('e.ultimo_estatus', 'ASC'); */

        $estatus = array(7, 8);

        $queryBuilder = $em->getRepository('FgimenezPlanillaBundle:Planilla')
                ->createQueryBuilder('e')
                ->where("e.ultimo_status in (:estatus)")
                ->setParameter('estatus', $estatus)
                ->orderBy('e.fecha_ultimo_estatus', 'DESC');


        $url = 'planilla_index_remitir';

        //$excedido[] = "";

        list($filterForm, $queryBuilder) = $this->filterAsignar($queryBuilder, $request);

        list($planillas, $pagerHtml, $existe) = $this->paginator($queryBuilder, $request, $url);

        $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        list($combo_analista1, $combo_analista2) = $planilla_repo->comboAnalista();

        if ($existe == 0) {

            $excedido = "";
        }

        foreach ($planillas as $planilla) {

            //se hace uso de la funcion fechaHabil para identificar
            //que un analista ya tiene este tiempo con un caso iniciado,
            //la funcion modifica el valor de $planilla
            //por lo que se debe setear nuevamente la fecha de ultimo estatus con el valor
            //de la posicion 0 del array fecha devuelto por la funcion



            $query = $em->getRepository('FgimenezPlanillaBundle:EstatusPlanilla')
                    ->createQueryBuilder('s')
                    ->select('MIN(s.created) AS created')
                    ->where('s.id_documento=:planilla and s.id_estatus=:estatus and s.AnalistaAsignado=:analista')
                    ->setParameter("planilla", $planilla->getId())
                    ->setParameter("estatus", $planilla->getUltimoStatus()->getId())
                    ->setParameter("analista", $planilla->getAnalistaAsignado());


            $resultado = $query->getQuery()->getResult();

            //$fecha_estatus = $resultado[0]['created'];

            $fecha_estatus = new \DateTime($resultado[0]['created']);

            $dias = 2;

            $fecha = $this->fechaHabil($fecha_estatus, $dias);

            $planilla->setfechaUltimoStatus($fecha[0]);

            $fecha_maxima = strtotime($fecha[$dias]);

            $fecha_actual = strtotime(date('Y-m-d'));

            if ($fecha_maxima <= $fecha_actual) {

                $valor = 'si';
            } else {

                $valor = 'no';
            }

            $excedido[] = $valor;

            $analista = $em->getRepository('dmanriqueUsuarioBundle:User')->findOneByUsername($planilla->getAnalistaAsignado());

            if ($analista) {

                $nombre_analista = $analista->getNombre1() . ' ' . $analista->getNombre2() . ' ' . $analista->getApellido1() . ' ' . $analista->getApellido2();

                $planilla->setAnalistaAsignado(ucwords(strtolower($nombre_analista)));
            }
        }

        return $this->render('FgimenezPlanillaBundle:Remitir:index.html.twig', array(
                    'planillas' => $planillas,
                    'pagerHtml' => $pagerHtml,
                    'filterForm' => $filterForm->createView(),
                    'excedido' => $excedido,
                    'analistas' => $combo_analista2,
                        //'nombre_analista'=>$nombre,
        ));
    }

    protected function filterRemitir($queryBuilder, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();

        $filterForm = $this->createForm('Fgimenez\PlanillaBundle\Form\RemitirFilterType');

        $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        list($comboAnalista, $combo_analista2) = $planilla_repo->comboAnalista();

        $filterForm->add('AnalistaAsignado', Filters\ChoiceFilterType::class, array(
            'label' => 'Analista',
            'required' => false,
            'choices' => $comboAnalista,
        ));



        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('PlanillaControllerFilterRemitir');
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
                $session->set('PlanillaControllerFilterRemitir', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('PlanillaControllerFilterRemitir')) {
                $filterData = $session->get('PlanillaControllerFilterRemitir');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('Fgimenez\PlanillaBundle\Form\RemitirFilterType', $filterData);

                /*                 * *********************************************************************** */
                /* cada vez que se cree el formulario de filter form se debe crear el campo analista*** */

                $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

                list($comboAnalista, $combo_analista2) = $planilla_repo->comboAnalista();

                $filterForm->add('AnalistaAsignado', Filters\ChoiceFilterType::class, array(
                    'label' => 'Analista',
                    'required' => false,
                    'choices' => $comboAnalista,
                ));
                /*                 * ***************************************** */

                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }

    public function remitirCasoAction(Request $request, ValoracionOAC $valoracionOAC) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_REMITIRCASO')) {

            throw $this->createAccessDeniedException();
        }

        $origen = $request->get('origen');

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('Fgimenez\PlanillaBundle\Form\RemitirType', $valoracionOAC);

        $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        $combo_instrucciones = $planilla_repo->comboInstrucciones();

        if ($valoracionOAC->getInstrucciones() == "") {

            $data = array(0);
        } else {

            $data = $valoracionOAC->getInstrucciones();
        }

        $form->add('instrucciones', ChoiceType::class, array(
            'label' => 'Instrucciones',
            'required' => true,
            'multiple' => true,
            'expanded' => true,
            'choices' => $combo_instrucciones,
            'data' => $data
        ));

        $form->handleRequest($request);

        $accion = $request->get('submit');

        $planilla = $valoracionOAC->getValoracionOACPlanilla();

        if ($accion != '') {

            $formulario = $request->request->get("remitir");

            if (!array_key_exists('instrucciones', $formulario)) {


                $this->get('session')->getFlashBag()->add('danger', 'Debe indicar al menos una instrucción');
                return $this->render('FgimenezPlanillaBundle:Remitir:new.html.twig', array(
                            'form' => $form->createView(),
                            'valoracion' => $valoracionOAC,
                ));
            }


            if ($accion == 'remitir_caso' || $accion == 'generar_nota') {

                $nota_definitiva = $em->getRepository('FgimenezPlanillaBundle:NotasRemisionDefinitivas')->findOneByOficioRemision($valoracionOAC->getOficioRemision());

                if ($nota_definitiva) {

                    $this->get('session')->getFlashBag()->add('danger', 'El número de oficio ya existe asociado a una nota de remisión. Verifique');

                    return $this->render('FgimenezPlanillaBundle:Remitir:new.html.twig', array(
                                'form' => $form->createView(),
                                'valoracion' => $valoracionOAC,
                                'origen' => $origen,
                    ));
                }


                $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(8);

                $editLink = $this->generateUrl('planilla_remision_pdf', array('id' => $valoracionOAC->getValoracionOACPlanilla()->getId()));

                $pdfPlanilla = '<script>var strWindowFeatures = "location=yes,height=570,width=520,scrollbars=yes,status=yes"; window.open("' . 'http://' . $_SERVER['SERVER_NAME'] . $editLink . '", "_blank", null);</script>';

                $mensaje = 'La valoración del caso' . $valoracionOAC->getValoracionOACPlanilla()->getCodigo() . ' se ha realizado exitosamente' . $pdfPlanilla;

                $this->get('session')->getFlashBag()->add('success', $mensaje);

                //se arma el objeto notas remision definitiva para insertar en la tabla donde estaran registradas
                //todas las notas de remision del caso

                $notasRemision = New NotasRemisionDefinitivas();

                $notasRemision->setAsuntoRemision($valoracionOAC->getAsuntoRemision());

                $notasRemision->setFechaNotaRemision(new \DateTime());

                $notasRemision->setInstrucciones(($formulario ['instrucciones']));

                $notasRemision->setNotasRemisionPlanilla($valoracionOAC->getValoracionOACPlanilla());

                $notasRemision->setObservacionRemision($valoracionOAC->getObservacionRemision());

                $notasRemision->setOficioRemision($valoracionOAC->getOficioRemision());

                $notasRemision->setAnalistaRemision($this->getUser()->getUsername());

                if ($accion == 'remitir_caso') {

                    $valoracionOAC->setFechaRemision(new \DateTime());
                }

                $valoracionOAC->setFechaNotaRemision(new \DateTime());

                $em->persist($notasRemision);
            }

            if ($accion == 'guardar') {

                $ultimo_estatus = $valoracionOAC->getValoracionOACPlanilla()->getUltimoStatus();

                $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById($ultimo_estatus->getId());

                $this->get('session')->getFlashBag()->add('success', 'Se han guardado los cambios de la nota de remisión' . $planilla->getCodigo());
            }


            $instrucciones = ($formulario ['instrucciones']);

            list($estatus_planilla, $planilla) = $this->registrarEstatusPlanilla($planilla, $estatus);

            $planilla->setUsuarioModificacion($this->getUser()->getUsername());

            $valoracionOAC->setAnalistaRemision($this->getUser()->getUsername());

            $valoracionOAC->setInstrucciones($instrucciones);

            $em->persist($planilla);

            $em->persist($valoracionOAC);

            $em->flush();

            return $this->redirectToRoute('planilla_index_remitir');
        }

        return $this->render('FgimenezPlanillaBundle:Remitir:new.html.twig', array(
                    'form' => $form->createView(),
                    'valoracion' => $valoracionOAC,
                    'origen' => $origen,
        ));
    }

    public function returnPDFResponseFromHTML($html, $origen) {

        /* Condicion para dibujar el footer en caso de que exista */

        if ($origen == 'nota_remision') {

            $pdf = $this->container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        }

        if ($origen == 'oficio_remision') {

            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        }



        $pdf->SetAuthor('User');
        $pdf->SetTitle(($origen));
        $pdf->SetSubject('Our');
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 11, '', true);
        $pdf->SetMargins(20, 20, 20, true);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(20);

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

    public function RemisionPDFAction(Planilla $planilla) {

        ob_clean();

        /* $usuario = $this->getDoctrine()
          ->getRepository('dmanriqueUsuarioBundle:User')
          ->findOneByUsername($solicitantes->getUsuarioCreacion()); */

        $em = $this->getDoctrine()->getManager();

        $id_compete = $planilla->getPlanillaValoracionOAC()->getCompetencia();

        if ($id_compete == 1) {

            $cgr = 'SI';

            $competencia = $em->getRepository('OrganizacionesBundle:organizaciones')->findOneById($planilla->getPlanillaValoracionOAC()->getOrganismoCompetencia());

            $compete = strtoupper($competencia->getNombre());

            $vista = 'FgimenezPlanillaBundle:Remitir:PDFRemision.html.twig';
        } else {

            $cgr = 'NO';

            $competencia = $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($planilla->getPlanillaValoracionOAC()->getOrganismoCompetencia());

            $compete = $competencia->getDesEnte();

            $vista = 'FgimenezPlanillaBundle:Remitir:PDFOficioRemision.html.twig';
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



        $planilla_repo = $em->getRepository("FgimenezPlanillaBundle:Planilla");

        $array_instrucciones = $planilla_repo->array_instrucciones($planilla->getPlanillaValoracionOAC()->getInstrucciones());




        $html = $this->renderView($vista, array(
            'planilla' => $planilla,
            'competencia' => $compete,
            'pertenece' => $cgr,
            'ente_presentado' => $ente,
            'atendido' => $analista_atendido['nombre_analista'],
            'revisado' => $analista_revisado['nombre_analista'],
            'array_instrucciones' => $array_instrucciones,
        ));

        $origen = "nota_remision";


        $this->returnPDFResponseFromHTML($html, $origen);
    }

    public function remitirCasoExternoAction(Request $request, ValoracionOAC $valoracionOAC) {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_REMITIRCASO')) {

            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();


        $form = $this->createForm('Fgimenez\PlanillaBundle\Form\RemitirEnteExternoType', $valoracionOAC);

        $form->handleRequest($request);

        $accion = $request->get('submit');


        if ($accion == "submit") {

            $planilla = $valoracionOAC->getValoracionOACPlanilla();

            $estatus = $em->getRepository('FgimenezPlanillaBundle:Estatus')->findOneById(8);

            list($estatus_planilla, $planilla) = $this->registrarEstatusPlanilla($planilla, $estatus);

            $planilla->setUsuarioModificacion($this->getUser()->getUsername());

            $valoracionOAC->setAnalistaRemision($this->getUser()->getUsername());

            $valoracionOAC->setFechaRemision(new \DateTime());

            $valoracionOAC->setFechaNotaRemision(new \DateTime());

            $em->persist($planilla);

            $em->persist($valoracionOAC);

            $em->flush();

            $editLink = $this->generateUrl('planilla_oficio_remision_pdf', array('id' => $valoracionOAC->getValoracionOACPlanilla()->getId()));

            $pdfPlanilla = '<script>var strWindowFeatures = "location=yes,height=570,width=520,scrollbars=yes,status=yes"; window.open("' . 'http://' . $_SERVER['SERVER_NAME'] . $editLink . '", "_blank", null);</script>';

            $mensaje = 'La remisión del caso' . $valoracionOAC->getValoracionOACPlanilla()->getCodigo() . ' se ha realizado exitosamente' . $pdfPlanilla;

            $this->get('session')->getFlashBag()->add('success', $mensaje);

            return $this->redirectToRoute('planilla_index_remitir');
        }

        return $this->render('FgimenezPlanillaBundle:Remitir:new_externo.html.twig', array(
                    'form' => $form->createView(),
                    'valoracion' => $valoracionOAC,
        ));
    }

    public function OficioRemisionPDFAction(Planilla $planilla) {

        ob_clean();


        $em = $this->getDoctrine()->getManager();

        $competencia = $em->getRepository('jsanchezInvolucradosBundle:Ente')->findOneByIdEnte($planilla->getPlanillaValoracionOAC()->getOrganismoCompetencia());

        $compete = $competencia->getDesEnte();

        $id_ente = $planilla->getPlanillaValoracionOAC()->getOrganismoCompetencia();

        $obj_enlace = $em->getRepository('Mmartin4MantenimientoBundle:EnlaceEnteExterno')->findOneByIdEnte($id_ente);

        $reporsitory = $em->getRepository("FgimenezPlanillaBundle:ValoracionOAC");

        $analista_atendido = $reporsitory->analistaPDF($planilla->getId(), 2);

        $analista_revisado = $reporsitory->analistaPDF($planilla->getId(), 7);

        $array_edo_mun_pquia = $reporsitory->array_edo_mun_pquia($id_ente);


        $html = $this->renderView('FgimenezPlanillaBundle:Remitir:PDFOficioRemision.html.twig', array(
            'planilla' => $planilla,
            'competencia' => strtolower($compete),
            //'pertenece' => $cgr,
            //'ente_presentado' => $ente,
            //'atendido' => $analista_atendido['nombre_analista'],
            'revisado' => $analista_revisado['nombre_analista'],
            //'array_instrucciones' => $array_instrucciones,
            'enlace' => $obj_enlace,
            'array_edo_mun_pquia' => $array_edo_mun_pquia,
        ));

        $origen = "oficio_remision";


        $this->returnPDFResponseFromHTML($html, $origen);
    }

}

class MYPDF extends \TCPDF {

    public function Footer() {


        // Position at 15 mm from bottom
        $this->SetY(-30);
        // Set font
        $this->SetFont('helvetica', '', 10);
        // Page number
        $this->Cell(0, 15, 'CONTRALORAS Y CONTRALORES SOMOS TODOS', 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->SetFont('helvetica', '', 8);
        $this->SetY(-25);

        $this->Cell(0, 15, 'Dirección: Caracas 1050, Avenida Andrés Bello. Apartado 1917', 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 6);
        $this->Cell(0, 15, 'ForCont 10A (08/2015) m.', 0, false, 'L', 0, '', 0, false, 'T', 'M');
    }

}
