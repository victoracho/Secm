<?php

namespace Fgimenez\DocumentoBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;
use Fgimenez\DocumentoBundle\Entity\Documento;
use Symfony\Component\Validator\Constraints as Assert;

//use Symfony\Component\HttpFoundation\BinaryFileResponse;
//use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Documento controller.
 *
 */
class DocumentoInterfazController extends Controller {

    public function nuevoAction(Request $request) {

        $this->enlace = $request->get('enlace');

        $ruta = explode("/", $this->enlace['ruta']);

        $caso = $ruta[2];

        $documento = new Documento();
        $form = $this->createForm('Fgimenez\DocumentoBundle\Form\DocumentoType', $documento);
        $form->handleRequest($request);


        return $this->render('FgimenezDocumentoBundle:DocumentoInterfaz:nuevo.html.twig', array(
                    'documento' => $documento,
                    'form' => $form->createView(),
                    'enlace' => $this->enlace,
                    'codigo' => $caso,
        ));
    }

    public function crearAction(Request $request) {

        //use \Fgimenez\PlanillaBundle\Entity\ExpedienteDocumento;
        $this->enlace = $request->get('enlace');

        $isAjax = $this->get('request_stack')->getCurrentRequest()->isXMLHttpRequest();
        if (!$isAjax) {            //return new Response('This is ajax response');
            return new Response('Esto no es ajax!', 400);
        }

        $documento = new Documento();
        $documento->setStatus(false);

        $documento = $this->get('app.datosUsuario')->setUsuarioCreacion($documento, $this->getUser()->getUsername());

        $form = $this->createForm('Fgimenez\DocumentoBundle\Form\DocumentoType', $documento);
        $form->handleRequest($request);

        $errorRuta = null;
        $tipo_documento_text = $form->get('tipo_documento_text')->getData();

        if ($form->isSubmitted() && $tipo_documento_text != 'Soporte Físico') {

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

            if ($tipo_documento_text != 'Soporte Físico') {
                $file = $documento->getRuta();
                $fileName = $this->get('app.file_uploader')->upload($file, $this->enlace['ruta'], null);
                $hash = $this->get('app.file_uploader')->md5($fileName);
                $documento->setRuta($fileName);
                $documento->setHash($hash);
            }

            $em = $this->getDoctrine()->getManager();

            $entidad_externo = $em->getRepository($this->enlace['entidad'])->find($this->enlace['id']);

            $entidad_externo->addDocumento($documento);


            $em->persist($entidad_externo);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', "El soporte fue cargado exitosamente.");
        } else {

            $this->get('session')->getFlashBag()->add('error', 'Ocurrió un error en la carga. Intente de nuevo.');

            //print_r($this->getErrorMessages($form));
            $this->mostrarErrores($documento);
        }

        return $this->mostrarAction($request);
    }

    public function mostrarAction(Request $request) {
        $this->enlace = $request->get('enlace');

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository($this->enlace['entidad']);

        $objeto = $repository->find($this->enlace['id']);


        return $this->render('FgimenezDocumentoBundle:DocumentoInterfaz:listado_documentos.html.twig', array(
                    'objeto' => $objeto,
                    'enlace' => $this->enlace,
        ));
    }

    private function getErrorMessages(\Symfony\Component\Form\Form $form) {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }

    public function eliminarAction(Request $request, Documento $documento) {
        $this->enlace = $request->get('enlace');

        $em = $this->getDoctrine()->getManager();
        $entidad_externo = $em->getRepository($this->enlace['entidad'])->find($this->enlace['id']);
        $entidad_externo->removeDocumento($documento);
        $em->persist($entidad_externo);
        $em->flush();
        return $this->mostrarAction($request);
    }

    public function consultarAction(Request $request, Documento $documento) {
        $this->enlace = $request->get('enlace');

        $em = $this->getDoctrine()->getManager();
        $documento = $em->getRepository('FgimenezDocumentoBundle:Documento')->getDocumentoArray($documento->getId());
        //echo '--------'.$documento->getRuta(); 
////transformar documento a json


        $response = new JsonResponse();
        $response->setData($documento);

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function modificarAction(Request $request, Documento $documento) {

        // en modificar no se toca el archivo, por eso esta comentado
        $this->enlace = $request->get('enlace');

        $isAjax = $this->get('request_stack')->getCurrentRequest()->isXMLHttpRequest();
        if (!$isAjax) {            //return new Response('This is ajax response');
            return new Response('Esto no es ajax!', 400);
        }



        $form = $this->createForm('Fgimenez\DocumentoBundle\Form\DocumentoType', $documento);
        $form->handleRequest($request);

        /* no borrar este bloque comentado, contiene la funcionalidad de edicion de archivo* */
        /*
          if ($form->isSubmitted()) {

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
          } */


        /* if ($form->isSubmitted()) {
          echo '1-submit';
          }

          if ($form->isValid()) {
          echo '2-valid';
          }

          if (!count($errorRuta)) {
          echo '3-count'.count($errorRuta);
          }
          exit; */

        if ($form->isSubmitted() && $form->isValid() && !count($errorRuta)) {


            /* no borrar este bloque comentado, contiene la funcionalidad de edicion de archivo* */
            /* $file = $documento->getRuta();

              if ($file)
              $fileName = $this->get('app.file_uploader')->upload($file, '/documento_soporte/'.$this->enlace['id'], $form->get('ruta2')->getData());
              else */
            $fileName = $form->get('ruta2')->getData();



            $documento->setRuta($fileName);
            /* $hash = $this->get('app.file_uploader')->md5($filename);
              $documento->setHash($hash);
             */

            $em = $this->getDoctrine()->getManager();

            $em->persist($documento);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', "El soporte fue modificado exitosamente.");
        } else {

            $this->get('session')->getFlashBag()->add('error', 'Ocurrió un error en la carga. Intente de nuevo.');
            $this->mostrarErrores($documento);
        }

        return $this->mostrarAction($request);
    }

    public function descargarAction(Request $request, Documento $documento) {

        $path = $documento->getRuta();

        if (!file_exists($path)) {
            //throw $this->createNotFoundException();
            $this->get('session')->getFlashBag()->add('error', 'El archivo no existe.');
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }
        //echo $path.'888';
        $path = explode('/web', $path);

        $pathReal = $_SERVER["DOCUMENT_ROOT"] . $path[1];
        $pathNavegador = 'http://' . $_SERVER['SERVER_NAME'] . $path[1];
        $temp = explode("/", $path[1]);
        $archivoNombre = end($temp);


        $hash = $this->get('app.file_uploader')->md5($pathReal);

        //echo  $hash.'---'.$documento->getHash();
        //echo  $pathReal.'---'.$pathNavegador; 
        //echo $pathReal.'---------' ;//.$_SERVER["DOCUMENT_ROOT"] . '/'.$archivoNombre;
        //exit;

        if ($hash == $documento->getHash()) {

            
            //se debe validar si la carpeta tiene el permiso correcto para descargar
            //para en caso de desarrollo se tiene permiso 777
                    
            $permiso=decoct(fileperms($_SERVER["DOCUMENT_ROOT"] . '/temp/') & 0777);
            
            if($permiso!=777){
                
                $this->get('session')->getFlashBag()->add('error', 'Error para descargar el archivo. Comuníquese con Sistemas.');
                return $this->redirect($_SERVER['HTTP_REFERER']);
                
            }
                       
            copy($pathReal, $_SERVER["DOCUMENT_ROOT"] . '/temp/' . $archivoNombre);
                   
           
        } else {

            $this->get('session')->getFlashBag()->add('error', 'El hashtag de verificación de archivo no coincide.');
            //echo $_SERVER['HTTP_REFERER'].'---'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
            //exit;
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }


        //return $response;
        return $this->redirect('http://' . $_SERVER['SERVER_NAME'] . '/temp/descargar.php?doc=' . $archivoNombre);
    }

    private function mostrarErrores($objeto) {
        $errors = $this->get('validator')->validate($objeto);
        $result = '';

        // iterate on it
        foreach ($errors as $error) {
            // Do stuff with:
            //   $error->getPropertyPath() : the field that caused the error
            //   $error->getMessage() : the error message
            $this->get('session')->getFlashBag()->add('error', $error->getMessage());
        }
    }

    public function verAction(Request $request) {
        $this->enlace = $request->get('enlace');

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository($this->enlace['entidad']);
        $objeto = $repository->find($this->enlace['id']);

        $codigo = $objeto->getCodigo();


        return $this->render('FgimenezDocumentoBundle:DocumentoInterfaz:listado_documentos_ver.html.twig', array(
                    'objeto' => $objeto,
                    'enlace' => $this->enlace,
        ));
    }

}

//7618340
