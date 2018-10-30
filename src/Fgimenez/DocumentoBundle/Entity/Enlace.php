<?php


namespace Fgimenez\DocumentoBundle\Entity;


use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 *
 * @ORM\MappedSuperclass
 * @UniqueEntity({"id_externo", "id_documento"})
 *
 */


abstract class Enlace 
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

  
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @var integer
     */
    private $id_externo;

    /**
     * @var integer
     */
    private $id_documento;


    /**
     * Set idExterno
     *
     * @param integer $idExterno
     *
     * @return Enlace
     */
    public function setIdExterno($idExterno)
    {
        $this->id_externo = $idExterno;

        return $this;
    }

    /**
     * Get idExterno
     *
     * @return integer
     */
    public function getIdExterno()
    {
        return $this->id_externo;
    }

    /**
     * Set idDocumento
     *
     * @param integer $idDocumento
     *
     * @return Enlace
     */
    public function setIdDocumento($idDocumento)
    {
        $this->id_documento = $idDocumento;

        return $this;
    }

    /**
     * Get idDocumento
     *
     * @return integer
     */
    public function getIdDocumento()
    {
        return $this->id_documento;
    }
    /**
     * @var \Fgimenez\DocumentoBundle\Entity\Documento
     */
    private $documento;


    /**
     * Set documento
     *
     * @param \Fgimenez\DocumentoBundle\Entity\Documento $documento
     *
     * @return Enlace
     */
    public function setDocumento(\Fgimenez\DocumentoBundle\Entity\Documento $documento = null)
    {
        $this->documento = $documento;

        return $this;
    }

    /**
     * Get documento
     *
     * @return \Fgimenez\DocumentoBundle\Entity\Documento
     */
    public function getDocumento()
    {
        return $this->documento;
    }
}
