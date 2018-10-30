<?php

namespace Fgimenez\DocumentoBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * TipoDocumento
 * @ORM\Entity
 * @UniqueEntity("nombre")
 * 
 */
class TipoDocumento 
{
  
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string 
     *   
     * @Assert\Regex(
     *     pattern="/[-.´*+?¿µ^${}()|!#%&=¡¨_½:;,°¬@·~¸`\[\]\/\\]/",
     *     match=false,
     *     message= "El campo Nombre no puede contener caracteres especiales"
     * ),  
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "El titulo debe ser {{ limit }} caracteres minimo",
     *      maxMessage = "El titulo no debe ser mas de {{ limit }} caracteres"
     * )
     */
    private $nombre;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var string
     */
    private $status;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \DateTime
     */
    private $createdAt = 'now()';

    /**
     * @var string
     */
    private $ultimoUsuario = '';


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return TipoDocumento
     */
    public function setNombre($nombre)
    {
        $this->nombre = strtolower($nombre);

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return TipoDocumento
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return TipoDocumento
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return TipoDocumento
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return TipoDocumento
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set ultimoUsuario
     *
     * @param string $ultimoUsuario
     *
     * @return TipoDocumento
     */
    public function setUltimoUsuario($ultimoUsuario)
    {
        $this->ultimoUsuario = $ultimoUsuario;

        return $this;
    }

    /**
     * Get ultimoUsuario
     *
     * @return string
     */
    public function getUltimoUsuario()
    {
        return $this->ultimoUsuario;
    }
    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
         //$this->ultimoUsuario = $this->get('security.token_storage')->getToken()->getUser()->getUsername();
         $this->ultimoUsuario = ' - ';
        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
         $this->uptdatedAt = new \DateTime();
         //$this->ultimoUsuario = $this->container->get('security.token_storage')->getToken()->getUser()->getUsername();
         $this->ultimoUsuario = ' - ';
        return $this;
    }
    
     /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getNombre();
    }
}
