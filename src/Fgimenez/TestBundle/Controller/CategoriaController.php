<?php

namespace Fgimenez\TestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;
use Fgimenez\TestBundle\Entity\Categoria;

/**
 * Categoria controller.
 *
 */
class CategoriaController extends Controller {

    /**
     * Lists all Categoria entities.
     *
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('FgimenezTestBundle:Categoria')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($categorias, $pagerHtml) = $this->paginator($queryBuilder, $request);

        return $this->render('FgimenezTestBundle:Categoria:index.html.twig', array(
                    'categorias' => $categorias,
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
        $filterForm = $this->createForm('Fgimenez\TestBundle\Form\CategoriaFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('CategoriaControllerFilter');
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
                $session->set('CategoriaControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('CategoriaControllerFilter')) {
                $filterData = $session->get('CategoriaControllerFilter');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('Fgimenez\TestBundle\Form\CategoriaFilterType', $filterData);
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
            return $me->generateUrl('categoria_crud', $requestParams);
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
     * Displays a form to create a new Categoria entity.
     *
     */
    public function newAction(Request $request) {

        $categorium = new Categoria();
        $form = $this->createForm('Fgimenez\TestBundle\Form\CategoriaType', $categorium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $categorium->getImagen();
            if ($file)
                $fileName = $this->get('app.file_uploader')->upload($file, '/test_categoria', null);
            else
                $fileName = $form->get('imagen2')->getData();
            $categorium->setImagen($fileName);

            $em = $this->getDoctrine()->getManager();

            $categorium->setTranslatableLocale($form->get('locale')->getData()); // change locale

            $em->persist($categorium);
            $em->flush();

            $editLink = $this->generateUrl('categoria_crud_edit', array('id' => $categorium->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New categorium was created successfully.</a>");

            $nextAction = $request->get('submit') == 'save' ? 'categoria_crud' : 'categoria_crud_new';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('FgimenezTestBundle:Categoria:new.html.twig', array(
                    'categorium' => $categorium,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Categoria entity.
     *
     */
    public function showAction(Categoria $categorium) {
        $deleteForm = $this->createDeleteForm($categorium);
        return $this->render('FgimenezTestBundle:Categoria:show.html.twig', array(
                    'categorium' => $categorium,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Categoria entity.
     *
     */
    public function editAction(Request $request, Categoria $categorium) {
        $deleteForm = $this->createDeleteForm($categorium);
        $editForm = $this->createForm('Fgimenez\TestBundle\Form\CategoriaType', $categorium);
        $editForm->handleRequest($request);

        if (!$editForm->isSubmitted())
            $editForm->get('imagen2')->setData($categorium->getImagen());

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $file = $categorium->getImagen();
            if ($file)
                $fileName = $this->get('app.file_uploader')->upload($file, '/test_categoria', $editForm->get('imagen2')->getData());
            else
                $fileName = $editForm->get('imagen2')->getData();
            $categorium->setImagen($fileName);


            $em = $this->getDoctrine()->getManager();
            $categorium->setTranslatableLocale($editForm->get('locale')->getData()); // change locale

            $em->persist($categorium);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('categoria_crud_edit', array('id' => $categorium->getId()));
        }
        return $this->render('FgimenezTestBundle:Categoria:edit.html.twig', array(
                    'categorium' => $categorium,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Categoria entity.
     *
     */
    public function deleteAction(Request $request, Categoria $categorium) {

        $form = $this->createDeleteForm($categorium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $categorium->getImagen();
            $this->get('app.file_uploader')->delete($file, '/test_categoria');
            $em = $this->getDoctrine()->getManager();
            $em->remove($categorium);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Categoria was deleted successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Categoria');
        }

        return $this->redirectToRoute('categoria_crud');
    }

    /**
     * Creates a form to delete a Categoria entity.
     *
     * @param Categoria $categorium The Categoria entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Categoria $categorium) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('categoria_crud_delete', array('id' => $categorium->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * Delete Categoria by id
     *
     */
    public function deleteByIdAction(Categoria $categorium) {
        $em = $this->getDoctrine()->getManager();

        try {
            $file = $categorium->getImagen();
            $this->get('app.file_uploader')->delete($file, '/test_categoria');
            $em->remove($categorium);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Categoria was deleted successfully');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Categoria');
        }

        return $this->redirect($this->generateUrl('categoria_crud'));
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
                $repository = $em->getRepository('FgimenezTestBundle:Categoria');

                foreach ($ids as $id) {
                    $categorium = $repository->find($id);

                    $file = $categorium->getImagen();
                    $this->get('app.file_uploader')->delete($file, '/test_categoria');

                    $em->remove($categorium);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'categorias was deleted successfully!');
            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the categorias ');
            }
        }

        return $this->redirect($this->generateUrl('categoria_crud'));
    }

}
