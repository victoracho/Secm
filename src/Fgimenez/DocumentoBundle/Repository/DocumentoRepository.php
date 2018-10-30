<?php

namespace Fgimenez\DocumentoBundle\Repository;

/**
 * DocumentoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DocumentoRepository extends \Doctrine\ORM\EntityRepository {

    public function getDocumentosByEntidad($enlace) {


        //$em = $this->getEntityManager();    


        $query = $this->createQueryBuilder('c')
                //$query = $em->getRepository("@FgimenezPlanillaBundle:ExpedienteDocumento")        
                ->select('a', 'b')
                ->from($enlace['entidad'], 'a')
                ->innerJoin("a.documento", 'b')
                ->where("a.id_externo = :id_externo")
                ->setParameter("id_externo", $enlace['id_externo'])
                //->setParameter("entidad", $entidad) 
                ->getQuery();

        return $query->getResult();

        //return 4;
    }

    public function getDocumentoArray($id_documento) {


        $em = $this->getEntityManager();    


        $query = $em->createQueryBuilder('d')
                ->select('d')
                ->from('FgimenezDocumentoBundle:Documento', 'd')
                ->where('d.id = :id')
                ->setParameter('id', $id_documento)
                ->getQuery();


        return $query->getArrayResult();

        //return 4;
    }

}
