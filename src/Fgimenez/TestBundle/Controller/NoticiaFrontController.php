<?php

namespace Fgimenez\TestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;
use Fgimenez\TestBundle\Entity\Noticia;

/**
 * Noticia Frontend controller.
 *
 */
class NoticiaFrontController extends Controller {

    /**
     * Lists all Noticia entities.
     *
     */
    public function indexAction(Request $request) {
       
    }

    public function noticiasAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('FgimenezTestBundle:Noticia')->createQueryBuilder('e');

        list($noticias, $pagerHtml) = $this->paginator($queryBuilder, $request);

        return $this->render('FgimenezTestBundle:Noticia:frontend/noticias.html.twig', array(
                    'noticias' => $noticias,
                    'pagerHtml' => $pagerHtml,
        ));
    }
    public function showAction(Request $request) {
        
        //echo $request->get('slug').'----------'.$request->getPathInfo();
        $repository = $this->getDoctrine()
                ->getRepository('FgimenezTestBundle:Noticia');


        $item = $repository->findOneBySlug($request->get('slug'));
        return $this->render('FgimenezTestBundle:Noticia:frontend/show.html.twig', array(
                    'item' => $item
        ));
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
        $pagerfanta->setMaxPerPage($request->get('pcg_show', 1));

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
            return $me->generateUrl('noticia_front', $requestParams);
        };

        // Paginator - view
        $view = new TwitterBootstrap3View();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'css_container_class' => 'pagination',
            'proximity' => 3,
            'prev_message' => 'Anterior <i class="fa fa-long-arrow-left"></i>',
            'next_message' => 'Siguiente <i class="fa fa-long-arrow-right"></i>',
        ));

        return array($entities, $pagerHtml);
    }

}
