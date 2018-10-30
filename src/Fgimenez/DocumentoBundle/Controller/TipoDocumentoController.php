<?php

namespace Fgimenez\DocumentoBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use Fgimenez\DocumentoBundle\Entity\TipoDocumento;

/**
 * TipoDocumento controller.
 *
 */
class TipoDocumentoController extends Controller
{
    /**
     * Lists all TipoDocumento entities.
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('FgimenezDocumentoBundle:TipoDocumento')->createQueryBuilder('e');
        
        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($tipoDocumentos, $pagerHtml) = $this->paginator($queryBuilder, $request);
        
        return $this->render('FgimenezDocumentoBundle:TipoDocumento:index.html.twig', array(
            'tipoDocumentos' => $tipoDocumentos,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),

        ));
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter($queryBuilder, Request $request)
    {
        $session = $request->getSession();
        $filterForm = $this->createForm('Fgimenez\DocumentoBundle\Form\TipoDocumentoFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('TipoDocumentoControllerFilter');
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
                $session->set('TipoDocumentoControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('TipoDocumentoControllerFilter')) {
                $filterData = $session->get('TipoDocumentoControllerFilter');
                
                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }
                
                $filterForm = $this->createForm('Fgimenez\DocumentoBundle\Form\TipoDocumentoFilterType', $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }


    /**
    * Get results from paginator and get paginator view.
    *
    */
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
            return $me->generateUrl('tipodocumento', $requestParams);
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
     * Displays a form to create a new TipoDocumento entity.
     *
     */
    public function newAction(Request $request)
    {
    
        $tipoDocumento = new TipoDocumento();
        $form   = $this->createForm('Fgimenez\DocumentoBundle\Form\TipoDocumentoType', $tipoDocumento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tipoDocumento);
            $em->flush();
            
            $editLink = $this->generateUrl('tipodocumento_edit', array('id' => $tipoDocumento->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>Creado exitosamente.</a>" );
            
            $nextAction=  $request->get('submit') == 'save' ? 'tipodocumento' : 'tipodocumento_new';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('FgimenezDocumentoBundle:TipoDocumento:new.html.twig', array(
            'tipoDocumento' => $tipoDocumento,
            'form'   => $form->createView(),
        ));
    }
    

    /**
     * Finds and displays a TipoDocumento entity.
     *
     */
    public function showAction(TipoDocumento $tipoDocumento)
    {
        $deleteForm = $this->createDeleteForm($tipoDocumento);
        return $this->render('FgimenezDocumentoBundle:TipoDocumento:show.html.twig', array(
            'tipoDocumento' => $tipoDocumento,
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    

    /**
     * Displays a form to edit an existing TipoDocumento entity.
     *
     */
    public function editAction(Request $request, TipoDocumento $tipoDocumento)
    {
        $deleteForm = $this->createDeleteForm($tipoDocumento);
        $editForm = $this->createForm('Fgimenez\DocumentoBundle\Form\TipoDocumentoType', $tipoDocumento);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tipoDocumento);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'Editado Exitosamente!');
            return $this->redirectToRoute('tipodocumento_edit', array('id' => $tipoDocumento->getId()));
        }
        return $this->render('FgimenezDocumentoBundle:TipoDocumento:edit.html.twig', array(
            'tipoDocumento' => $tipoDocumento,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    

    /**
     * Deletes a TipoDocumento entity.
     *
     */
    public function deleteAction(Request $request, TipoDocumento $tipoDocumento)
    {
    
        $form = $this->createDeleteForm($tipoDocumento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tipoDocumento);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'TipoDocumento fue eliminado exitosamente');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con TipoDocumento');
        }
        
        return $this->redirectToRoute('tipodocumento');
    }
    
    /**
     * Creates a form to delete a TipoDocumento entity.
     *
     * @param TipoDocumento $tipoDocumento The TipoDocumento entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(TipoDocumento $tipoDocumento)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('tipodocumento_delete', array('id' => $tipoDocumento->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     * Delete TipoDocumento by id
     *
     */
    public function deleteByIdAction(TipoDocumento $tipoDocumento){
        $em = $this->getDoctrine()->getManager();
        
        try {
            $em->remove($tipoDocumento);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'TipoDocumento fue eliminado exitosamente');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con TipoDocumento');
        }

        return $this->redirect($this->generateUrl('tipodocumento'));

    }
    

    /**
    * Bulk Action
    */
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('FgimenezDocumentoBundle:TipoDocumento');

                foreach ($ids as $id) {
                    $tipoDocumento = $repository->find($id);
                    $em->remove($tipoDocumento);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'tipoDocumentos was deleted successfully!');

            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the tipoDocumentos ');
            }
        }

        return $this->redirect($this->generateUrl('tipodocumento'));
    }
    

}
