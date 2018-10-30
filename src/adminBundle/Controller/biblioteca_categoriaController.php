<?php

namespace adminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use adminBundle\Entity\biblioteca_categoria;

/**
 * biblioteca_categoria controller.
 *
 * @Route("/biblioteca_categoria")
 */
class biblioteca_categoriaController extends Controller
{
    /**
     * Lists all biblioteca_categoria entities.
     *
     * @Route("/", name="biblioteca_categoria")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('adminBundle:biblioteca_categoria')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
          $totalOfRecordsString = $this->getTotalOfRecordsString(clone $queryBuilder, $request);
        list($biblioteca_categorias, $pagerHtml) = $this->paginator($queryBuilder, $request);



        return $this->render('biblioteca_categoria/index.html.twig', array(
            'biblioteca_categorias' => $biblioteca_categorias,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'totalOfRecordsString' => $totalOfRecordsString,

        ));
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter($queryBuilder, Request $request)
    {
        $session = $request->getSession();
        $filterForm = $this->createForm('adminBundle\Form\biblioteca_categoriaFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('biblioteca_categoriaControllerFilter');
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
                $session->set('biblioteca_categoriaControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('biblioteca_categoriaControllerFilter')) {
                $filterData = $session->get('biblioteca_categoriaControllerFilter');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('adminBundle\Form\biblioteca_categoriaFilterType', $filterData);
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
            return $me->generateUrl('biblioteca_categoria', $requestParams);
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



    /*
     * Calculates the total of records string
     */
    protected function getTotalOfRecordsString($queryBuilder, $request) {
        $totalOfRecords = $queryBuilder->select('COUNT(e.id)')->getQuery()->getSingleScalarResult();
        $show = $request->get('pcg_show', 10);
        $page = $request->get('pcg_page', 1);

        $startRecord = ($show * ($page - 1)) + 1;
        $endRecord = $show * $page;

        if ($endRecord > $totalOfRecords) {
            $endRecord = $totalOfRecords;
        }
        return "Showing $startRecord - $endRecord of $totalOfRecords Records.";
    }



    /**
     * Displays a form to create a new biblioteca_categoria entity.
     *
     * @Route("/new", name="biblioteca_categoria_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {

        $biblioteca_categorium = new biblioteca_categoria();
        $form   = $this->createForm('adminBundle\Form\biblioteca_categoriaType', $biblioteca_categorium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($biblioteca_categorium);
            $em->flush();

            $editLink = $this->generateUrl('biblioteca_categoria_edit', array('id' => $biblioteca_categorium->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New biblioteca_categorium was created successfully.</a>" );

            $nextAction=  $request->get('submit') == 'save' ? 'biblioteca_categoria' : 'biblioteca_categoria_new';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('biblioteca_categoria/new.html.twig', array(
            'biblioteca_categorium' => $biblioteca_categorium,
            'form'   => $form->createView(),
        ));
    }


    /**
     * Finds and displays a biblioteca_categoria entity.
     *
     * @Route("/{id}", name="biblioteca_categoria_show")
     * @Method("GET")
     */
    public function showAction(biblioteca_categoria $biblioteca_categorium)
    {
        $deleteForm = $this->createDeleteForm($biblioteca_categorium);
        return $this->render('biblioteca_categoria/show.html.twig', array(
            'biblioteca_categorium' => $biblioteca_categorium,
            'delete_form' => $deleteForm->createView(),
        ));
    }



    /**
     * Displays a form to edit an existing biblioteca_categoria entity.
     *
     * @Route("/{id}/edit", name="biblioteca_categoria_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, biblioteca_categoria $biblioteca_categorium)
    {
        $deleteForm = $this->createDeleteForm($biblioteca_categorium);
        $editForm = $this->createForm('adminBundle\Form\biblioteca_categoriaType', $biblioteca_categorium);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($biblioteca_categorium);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('biblioteca_categoria_edit', array('id' => $biblioteca_categorium->getId()));
        }
        return $this->render('biblioteca_categoria/edit.html.twig', array(
            'biblioteca_categorium' => $biblioteca_categorium,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }



    /**
     * Deletes a biblioteca_categoria entity.
     *
     * @Route("/{id}", name="biblioteca_categoria_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, biblioteca_categoria $biblioteca_categorium)
    {

        $form = $this->createDeleteForm($biblioteca_categorium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($biblioteca_categorium);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The biblioteca_categoria was deleted successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the biblioteca_categoria');
        }

        return $this->redirectToRoute('biblioteca_categoria');
    }

    /**
     * Creates a form to delete a biblioteca_categoria entity.
     *
     * @param biblioteca_categoria $biblioteca_categorium The biblioteca_categoria entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(biblioteca_categoria $biblioteca_categorium)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('biblioteca_categoria_delete', array('id' => $biblioteca_categorium->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Delete biblioteca_categoria by id
     *
     * @Route("/delete/{id}", name="biblioteca_categoria_by_id_delete")
     * @Method("GET")
     */
    public function deleteByIdAction(biblioteca_categoria $biblioteca_categorium){
        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($biblioteca_categorium);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The biblioteca_categoria was deleted successfully');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the biblioteca_categoria');
        }

        return $this->redirect($this->generateUrl('biblioteca_categoria'));

    }


    /**
    * Bulk Action
    * @Route("/bulk-action/", name="biblioteca_categoria_bulk_action")
    * @Method("POST")
    */
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('adminBundle:biblioteca_categoria');

                foreach ($ids as $id) {
                    $biblioteca_categorium = $repository->find($id);
                    $em->remove($biblioteca_categorium);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'biblioteca_categorias was deleted successfully!');

            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the biblioteca_categorias ');
            }
        }

        return $this->redirect($this->generateUrl('biblioteca_categoria'));
    }


}
