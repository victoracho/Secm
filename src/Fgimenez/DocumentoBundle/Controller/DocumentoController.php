<?php

namespace Fgimenez\DocumentoBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;
use Fgimenez\DocumentoBundle\Entity\Documento;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Documento controller.
 *
 */
class DocumentoController extends Controller {

    /**
     * Lists all Documento entities.
     *
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('FgimenezDocumentoBundle:Documento')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($documentos, $pagerHtml) = $this->paginator($queryBuilder, $request);

        return $this->render('FgimenezDocumentoBundle:Documento:index.html.twig', array(
                    'documentos' => $documentos,
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
        $filterForm = $this->createForm('Fgimenez\DocumentoBundle\Form\DocumentoFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('DocumentoControllerFilter');
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
                $session->set('DocumentoControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('DocumentoControllerFilter')) {
                $filterData = $session->get('DocumentoControllerFilter');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('Fgimenez\DocumentoBundle\Form\DocumentoFilterType', $filterData);
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
            return $me->generateUrl('documento_crud', $requestParams);
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
     * Displays a form to create a new Documento entity.
     *
     */
    public function newAction(Request $request) {

        $documento = new Documento();
        $form = $this->createForm('Fgimenez\DocumentoBundle\Form\DocumentoType', $documento);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $file = $documento->getRuta();
            $rutaConstraint = new Assert\NotNull();
            //$rutaConstraint->message = "Debe subir un documento";

            $errorRuta = $this->get("validator")->validate(
                    $file, $rutaConstraint
            );


            if (count($errorRuta)) {
                $this->get('session')->getFlashBag()->add('error', "Debe subir un documento");
            }
        }





        if ($form->isSubmitted() && $form->isValid() && !count($errorRuta)) {
            $file = $documento->getRuta();
            //if ($file) {
            $fileName = $this->get('app.file_uploader')->upload($file, '/documento_documento', null);
            //}


            $documento->setRuta($fileName);


            $em = $this->getDoctrine()->getManager();
            $em->persist($documento);
            $em->flush();

            $editLink = $this->generateUrl('documento_crud_edit', array('id' => $documento->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>documento fue creado exitosamente.</a>");

            $nextAction = $request->get('submit') == 'save' ? 'documento_crud' : 'documento_crud_new';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('FgimenezDocumentoBundle:Documento:new.html.twig', array(
                    'documento' => $documento,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Documento entity.
     *
     */
    public function showAction(Documento $documento) {
        $deleteForm = $this->createDeleteForm($documento);
        return $this->render('FgimenezDocumentoBundle:Documento:show.html.twig', array(
                    'documento' => $documento,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Documento entity.
     *
     */
    public function editAction(Request $request, Documento $documento) {
        $deleteForm = $this->createDeleteForm($documento);
        $editForm = $this->createForm('Fgimenez\DocumentoBundle\Form\DocumentoType', $documento);
        $editForm->handleRequest($request);



        if (!$editForm->isSubmitted())
            $editForm->get('ruta2')->setData($documento->getRuta());



        if ($editForm->isSubmitted()) {

            $file = $documento->getRuta();
            $errorRuta = null;

            if ($file) {
                $rutaConstraint = new Assert\NotNull();
                //$rutaConstraint->message = "Debe subir un documento";

                $errorRuta = $this->get("validator")->validate(
                        $file, $rutaConstraint
                );

                if (count($errorRuta)) {
                    $this->get('session')->getFlashBag()->add('error', "Debe subir un documento");
                }
            }
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $file = $documento->getRuta();
            if ($file)
                $fileName = $this->get('app.file_uploader')->upload($file, '/documento_documento', $form->get('ruta2')->getData());
            else
                $fileName = $form->get('ruta2')->getData();

            $documento->setRuta($fileName);
            $em = $this->getDoctrine()->getManager();
            $em->persist($documento);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Editado Exitosamente!');
            return $this->redirectToRoute('documento_crud_edit', array('id' => $documento->getId()));
        }
        return $this->render('FgimenezDocumentoBundle:Documento:edit.html.twig', array(
                    'documento' => $documento,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Documento entity.
     *
     */
    public function deleteAction(Request $request, Documento $documento) {

        $form = $this->createDeleteForm($documento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($documento);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Documento fue eliminado exitosamente');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con Documento');
        }

        return $this->redirectToRoute('documento_crud');
    }

    /**
     * Creates a form to delete a Documento entity.
     *
     * @param Documento $documento The Documento entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Documento $documento) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('documento_crud_delete', array('id' => $documento->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * Delete Documento by id
     *
     */
    public function deleteByIdAction(Documento $documento) {
        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($documento);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Documento fue eliminado exitosamente');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con Documento');
        }

        return $this->redirect($this->generateUrl('documento_crud'));
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
                $repository = $em->getRepository('FgimenezDocumentoBundle:Documento');

                foreach ($ids as $id) {
                    $documento = $repository->find($id);
                    $em->remove($documento);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'documentos was deleted successfully!');
            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the documentos ');
            }
        }

        return $this->redirect($this->generateUrl('documento_crud'));
    }

}
