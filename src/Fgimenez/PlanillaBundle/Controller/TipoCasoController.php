<?php

namespace Fgimenez\PlanillaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;
use Fgimenez\PlanillaBundle\Entity\TipoCaso;

/**
 * TipoCaso controller.
 *
 */
class TipoCasoController extends Controller {

    /**
     * Lists all TipoCaso entities.
     *
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('FgimenezPlanillaBundle:TipoCaso')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($tipoCasos, $pagerHtml) = $this->paginator($queryBuilder, $request);

        return $this->render('FgimenezPlanillaBundle:TipoCaso:index.html.twig', array(
                    'tipoCasos' => $tipoCasos,
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
        $filterForm = $this->createForm('Fgimenez\PlanillaBundle\Form\TipoCasoFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('TipoCasoControllerFilter');
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
                $session->set('TipoCasoControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('TipoCasoControllerFilter')) {
                $filterData = $session->get('TipoCasoControllerFilter');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('Fgimenez\PlanillaBundle\Form\TipoCasoFilterType', $filterData);
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
        $sortCol = $queryBuilder->getRootAlias() . '.' . $request->get('pcg_sort_col', 'id');
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
            return $me->generateUrl('tipocaso', $requestParams);
        };

        // Paginator - view
        $view = new TwitterBootstrap3View();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => 'anterior',
            'next_message' => 'siguiente',
        ));

        return array($entities, $pagerHtml);
    }

    /**
     * Displays a form to create a new TipoCaso entity.
     *
     */
    public function newAction(Request $request) {

        $tipoCaso = new TipoCaso();
        $form = $this->createForm('Fgimenez\PlanillaBundle\Form\TipoCasoType', $tipoCaso);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tipoCaso);

            //se dene inicializar en cero la tabla correlativo_requerimiento.

            $repository = $em->getRepository("FgimenezPlanillaBundle:TipoCaso");

            $resultado = $repository->InicializarCorrelativo($tipoCaso->getId());

    
            if ($resultado == 1) {

                $em->flush();

                $editLink = $this->generateUrl('tipocaso_edit', array('id' => $tipoCaso->getId()));
                $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>Creado exitosamente.</a>");

                $nextAction = $request->get('submit') == 'save' ? 'tipocaso' : 'tipocaso_new';
                return $this->redirectToRoute($nextAction);
            
                
            }else{
                
               $this->get('session')->getFlashBag()->add('danger', "No se creó en tipo de reuqerimiento. Intente Nuevamente");  
                
            }
        }
        return $this->render('FgimenezPlanillaBundle:TipoCaso:new.html.twig', array(
                    'tipoCaso' => $tipoCaso,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a TipoCaso entity.
     *
     */
    public function showAction(TipoCaso $tipoCaso) {
        $deleteForm = $this->createDeleteForm($tipoCaso);
        return $this->render('FgimenezPlanillaBundle:TipoCaso:show.html.twig', array(
                    'tipoCaso' => $tipoCaso,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing TipoCaso entity.
     *
     */
    public function editAction(Request $request, TipoCaso $tipoCaso) {
        $deleteForm = $this->createDeleteForm($tipoCaso);
        $editForm = $this->createForm('Fgimenez\PlanillaBundle\Form\TipoCasoType', $tipoCaso);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tipoCaso);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Editado Exitosamente!');
            return $this->redirectToRoute('tipocaso_edit', array('id' => $tipoCaso->getId()));
        }
        return $this->render('FgimenezPlanillaBundle:TipoCaso:edit.html.twig', array(
                    'tipoCaso' => $tipoCaso,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a TipoCaso entity.
     *
     */
    public function deleteAction(Request $request, TipoCaso $tipoCaso) {

        $form = $this->createDeleteForm($tipoCaso);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tipoCaso);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'TipoCaso fue eliminado exitosamente');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con TipoCaso');
        }

        return $this->redirectToRoute('tipocaso');
    }

    /**
     * Creates a form to delete a TipoCaso entity.
     *
     * @param TipoCaso $tipoCaso The TipoCaso entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(TipoCaso $tipoCaso) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('tipocaso_delete', array('id' => $tipoCaso->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * Delete TipoCaso by id
     *
     */
    public function deleteByIdAction(TipoCaso $tipoCaso) {
        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($tipoCaso);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'TipoCaso fue eliminado exitosamente');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con TipoCaso');
        }

        return $this->redirect($this->generateUrl('tipocaso'));
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
                $repository = $em->getRepository('FgimenezPlanillaBundle:TipoCaso');

                foreach ($ids as $id) {
                    $tipoCaso = $repository->find($id);
                    $em->remove($tipoCaso);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'tipoCasos was deleted successfully!');
            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the tipoCasos ');
            }
        }

        return $this->redirect($this->generateUrl('tipocaso'));
    }

}
