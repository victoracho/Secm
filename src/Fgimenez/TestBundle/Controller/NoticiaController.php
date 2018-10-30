<?php

namespace Fgimenez\TestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;
use Fgimenez\TestBundle\Entity\Noticia;

/**
 * Noticia controller.
 *
 */
class NoticiaController extends Controller {

    /**
     * Lists all Noticia entities.
     *
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('FgimenezTestBundle:Noticia')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($noticias, $pagerHtml) = $this->paginator($queryBuilder, $request);

        return $this->render('FgimenezTestBundle:Noticia:index.html.twig', array(
                    'noticias' => $noticias,
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
        $filterForm = $this->createForm('Fgimenez\TestBundle\Form\NoticiaFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('NoticiaControllerFilter');
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
                $session->set('NoticiaControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('NoticiaControllerFilter')) {
                $filterData = $session->get('NoticiaControllerFilter');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('Fgimenez\TestBundle\Form\NoticiaFilterType', $filterData);
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
            return $me->generateUrl('noticia_crud', $requestParams);
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
     * Displays a form to create a new Noticia entity.
     *
     */
    public function newAction(Request $request) {

        $noticium = new Noticia();
        $form = $this->createForm('Fgimenez\TestBundle\Form\NoticiaType', $noticium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $file = $noticium->getImagen();
            if ($file)
                $fileName = $this->get('app.file_uploader')->upload($file, '/test_noticia', null);
            else
                $fileName = $form->get('imagen2')->getData();
            $noticium->setImagen($fileName);

            $em = $this->getDoctrine()->getManager();
            $noticium->setTranslatableLocale($form->get('locale')->getData()); // change locale
            $em->persist($noticium);
            $em->flush();

            $editLink = $this->generateUrl('noticia_crud_edit', array('id' => $noticium->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New noticium was created successfully.</a>");

            $nextAction = $request->get('submit') == 'save' ? 'noticia_crud' : 'noticia_crud_new';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('FgimenezTestBundle:Noticia:new.html.twig', array(
                    'noticium' => $noticium,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Noticia entity.
     *
     */
    public function showAction(Noticia $noticium) {
        $deleteForm = $this->createDeleteForm($noticium);
        return $this->render('FgimenezTestBundle:Noticia:show.html.twig', array(
                    'noticium' => $noticium,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Noticia entity.
     *
     */
    public function editAction(Request $request, Noticia $noticium) {
        $deleteForm = $this->createDeleteForm($noticium);
        $editForm = $this->createForm('Fgimenez\TestBundle\Form\NoticiaType', $noticium);
        $editForm->handleRequest($request);

        if (!$editForm->isSubmitted())
            $editForm->get('imagen2')->setData($noticium->getImagen());

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $file = $noticium->getImagen();
            if ($file)
                $fileName = $this->get('app.file_uploader')->upload($file, '/test_noticia', $editForm->get('imagen2')->getData());
            else
                $fileName = $editForm->get('imagen2')->getData();
            $noticium->setImagen($fileName);

            $em = $this->getDoctrine()->getManager();
            $noticium->setTranslatableLocale($editForm->get('locale')->getData()); // change locale
            $em->persist($noticium);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('noticia_crud_edit', array('id' => $noticium->getId()));
        }
        return $this->render('FgimenezTestBundle:Noticia:edit.html.twig', array(
                    'noticium' => $noticium,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Noticia entity.
     *
     */
    public function deleteAction(Request $request, Noticia $noticium) {

        $form = $this->createDeleteForm($noticium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $noticium->getImagen();
            $this->get('app.file_uploader')->delete($file, '/test_noticia');
            $em = $this->getDoctrine()->getManager();
            $em->remove($noticium);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Noticia was deleted successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Noticia');
        }

        return $this->redirectToRoute('noticia_crud');
    }

    /**
     * Creates a form to delete a Noticia entity.
     *
     * @param Noticia $noticium The Noticia entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Noticia $noticium) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('noticia_crud_delete', array('id' => $noticium->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * Delete Noticia by id
     *
     */
    public function deleteByIdAction(Noticia $noticium) {
        $em = $this->getDoctrine()->getManager();

        try {
            $file = $noticium->getImagen();
            $this->get('app.file_uploader')->delete($file, '/test_noticia');
            $em->remove($noticium);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Noticia was deleted successfully');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Noticia');
        }

        return $this->redirect($this->generateUrl('noticia_crud'));
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
                $repository = $em->getRepository('FgimenezTestBundle:Noticia');

                foreach ($ids as $id) {

                    $noticium = $repository->find($id);
                    $file = $noticium->getImagen();
                    $this->get('app.file_uploader')->delete($file, '/test_noticia');
                    $em->remove($noticium);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'noticias was deleted successfully!');
            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the noticias ');
            }
        }

        return $this->redirect($this->generateUrl('noticia_crud'));
    }

}
