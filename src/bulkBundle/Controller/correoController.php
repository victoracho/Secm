<?php

namespace bulkBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use bulkBundle\Entity\correo;

/**
 * correo controller.
 *
 */
class correoController extends Controller
{
    /**
     * Lists all correo entities.
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('bulkBundle:correo')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($correos, $pagerHtml) = $this->paginator($queryBuilder, $request);

        return $this->render('bulkBundle:correo:index.html.twig', array(
            'correos' => $correos,
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
        $filterForm = $this->createForm('bulkBundle\Form\correoFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('correoControllerFilter');
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
                $session->set('correoControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('correoControllerFilter')) {
                $filterData = $session->get('correoControllerFilter');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('bulkBundle\Form\correoFilterType', $filterData);
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
            return $me->generateUrl('correo', $requestParams);
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
     * Displays a form to create a new correo entity.
     *
     */
    public function newAction(Request $request)
    {

        $correo = new correo();
        $form   = $this->createForm('bulkBundle\Form\correoType', $correo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        $html=$form['texto']->getdata();
        $array['fecha']= $form['fecha']->getdata();
        $array['asunto']=$form['asunto']->getdata();
            $em = $this->getDoctrine()->getManager();
            $em->persist($correo);
            $em->flush();

        $response = $this->forward('bulkBundle:prueba:enviar',
         array(
       'html'  => $html,
        'array'=> $array));

            return $response;

        }
        return $this->render('bulkBundle:correo:new.html.twig', array(
            'correo' => $correo,
            'form'   => $form->createView(),
        ));
    }


    /**
     * Finds and displays a correo entity.
     *
     */
    public function showAction(correo $correo)
    {
        $deleteForm = $this->createDeleteForm($correo);
        return $this->render('bulkBundle:correo:show.html.twig', array(
            'correo' => $correo,
            'delete_form' => $deleteForm->createView(),
        ));
    }



    /**
     * Displays a form to edit an existing correo entity.
     *
     */
    public function editAction(Request $request, correo $correo)
    {
        $deleteForm = $this->createDeleteForm($correo);
        $editForm = $this->createForm('bulkBundle\Form\correoType', $correo);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($correo);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Editado Exitosamente!');
            return $this->redirectToRoute('correo_edit', array('id' => $correo->getId()));
        }
        return $this->render('bulkBundle:correo:edit.html.twig', array(
            'correo' => $correo,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }



    /**
     * Deletes a correo entity.
     *
     */
    public function deleteAction(Request $request, correo $correo)
    {

        $form = $this->createDeleteForm($correo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($correo);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'correo fue eliminado exitosamente');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con correo');
        }

        return $this->redirectToRoute('correo');
    }

    /**
     * Creates a form to delete a correo entity.
     *
     * @param correo $correo The correo entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(correo $correo)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('correo_delete', array('id' => $correo->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Delete correo by id
     *
     */
    public function deleteByIdAction(correo $correo){
        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($correo);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'correo fue eliminado exitosamente');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un problema con correo');
        }

        return $this->redirect($this->generateUrl('correo'));

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
                $repository = $em->getRepository('bulkBundle:correo');

                foreach ($ids as $id) {
                    $correo = $repository->find($id);
                    $em->remove($correo);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'correos was deleted successfully!');

            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the correos ');
            }
        }

        return $this->redirect($this->generateUrl('correo'));
    }


}
