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
use CGRIntranet\adminBundle\Service\FileUploader;
/**
 * biblioteca controller.
 *
 * @Route("/biblioteca")
 */
class bibliotecaController extends Controller
{
    /**
     * Lists all biblioteca entities.
     *
     * @Route("/", name="biblioteca")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('adminBundle:biblioteca')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
          $totalOfRecordsString = $this->getTotalOfRecordsString(clone $queryBuilder, $request);
        list($bibliotecas, $pagerHtml) = $this->paginator($queryBuilder, $request);


        return $this->render('biblioteca/index.html.twig', array(
            'bibliotecas' => $bibliotecas,
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
        $filterForm = $this->createForm('adminBundle\Form\bibliotecaFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('bibliotecaControllerFilter');
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
                $session->set('bibliotecaControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('bibliotecaControllerFilter')) {
                $filterData = $session->get('bibliotecaControllerFilter');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('adminBundle\Form\bibliotecaFilterType', $filterData);
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
            return $me->generateUrl('biblioteca', $requestParams);
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
     * Displays a form to create a new biblioteca entity.
     *
     * @Route("/new", name="biblioteca_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {

        $biblioteca = new biblioteca();
        $form   = $this->createForm('adminBundle\Form\bibliotecaType', $biblioteca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $biblioteca->getBrochure();
            $fileName = $this->get('adminBundle\Service\FileUploader')->upload($file);
            $biblioteca->setBrochure($fileName);
            $em = $this->getDoctrine()->getManager();
            $em->persist($biblioteca);
            $em->flush();

            $editLink = $this->generateUrl('biblioteca_edit', array('id' => $biblioteca->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New biblioteca was created successfully.</a>" );

            $nextAction=  $request->get('submit') == 'save' ? 'biblioteca' : 'biblioteca_new';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('biblioteca/new.html.twig', array(
            'biblioteca' => $biblioteca,
            'form'   => $form->createView(),
        ));
    }


    /**
     * Finds and displays a biblioteca entity.
     *
     * @Route("/{id}", name="biblioteca_show")
     * @Method("GET")
     */
    public function showAction(biblioteca $biblioteca)
    {
        $deleteForm = $this->createDeleteForm($biblioteca);
        return $this->render('biblioteca/show.html.twig', array(
            'biblioteca' => $biblioteca,
            'delete_form' => $deleteForm->createView(),
        ));
    }



    /**
     * Displays a form to edit an existing biblioteca entity.
     *
     * @Route("/{id}/edit", name="biblioteca_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, biblioteca $biblioteca)
    {
        $deleteForm = $this->createDeleteForm($biblioteca);
        $editForm = $this->createForm('adminBundle\Form\bibliotecaType', $biblioteca);
        $editForm->handleRequest($request);

        if (!$editForm->isSubmitted())
        $editForm->get('pdf2')->setData($biblioteca->getBrochure());
        if ($editForm->isSubmitted() && $editForm->isValid()) {

         $file = $biblioteca->getBrochure();
         if ($file)
             $fileName = $this->get('adminBundle\Service\FileUploader')->upload($file, '/web/uploads/brochure', $editForm->get('pdf2')->getData());
         else
             $fileName = $editForm->get('pdf2')->getData();
         $biblioteca->setBrochure($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($biblioteca);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('biblioteca_edit', array('id' => $biblioteca->getId()));
        }
        return $this->render('biblioteca/edit.html.twig', array(
            'biblioteca' => $biblioteca,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }



    /**
     * Deletes a biblioteca entity.
     *
     * @Route("/{id}", name="biblioteca_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, biblioteca $biblioteca)
    {

        $form = $this->createDeleteForm($biblioteca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($biblioteca);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The biblioteca was deleted successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the biblioteca');
        }

        return $this->redirectToRoute('biblioteca');
    }

    /**
     * Creates a form to delete a biblioteca entity.
     *
     * @param biblioteca $biblioteca The biblioteca entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(biblioteca $biblioteca)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('biblioteca_delete', array('id' => $biblioteca->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Delete biblioteca by id
     *
     * @Route("/delete/{id}", name="biblioteca_by_id_delete")
     * @Method("GET")
     */
    public function deleteByIdAction(biblioteca $biblioteca){
        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($biblioteca);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The biblioteca was deleted successfully');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the biblioteca');
        }

        return $this->redirect($this->generateUrl('biblioteca'));

    }


    /**
    * Bulk Action
    * @Route("/bulk-action/", name="biblioteca_bulk_action")
    * @Method("POST")
    */
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('adminBundle:biblioteca');

                foreach ($ids as $id) {
                    $biblioteca = $repository->find($id);
                    $em->remove($biblioteca);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'bibliotecas was deleted successfully!');

            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the bibliotecas ');
            }
        }

        return $this->redirect($this->generateUrl('biblioteca'));
    }


}
