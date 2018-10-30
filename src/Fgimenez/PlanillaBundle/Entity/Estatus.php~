<?php

namespace Fgimenez\PlanillaBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Timestampable;

/**
 * Estatus
 * @ORM\Entity
 * @UniqueEntity("nombre")
 * 
 */
class Estatus 
{
  
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string  
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
     * @var integer
     */
    private $status;



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
     * @return Estatus
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

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
     * @return Estatus
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
     * @param integer $status
     *
     * @return Estatus
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    
  
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $EstatusPlanilla;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->EstatusPlanilla = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add estatusPlanilla
     *
     * @param \Fgimenez\PlanillaBundle\Entity\Planilla $estatusPlanilla
     *
     * @return Estatus
     */
    public function addEstatusPlanilla(\Fgimenez\PlanillaBundle\Entity\Planilla $estatusPlanilla)
    {
        $this->EstatusPlanilla[] = $estatusPlanilla;

        return $this;
    }

    /**
     * Remove estatusPlanilla
     *
     * @param \Fgimenez\PlanillaBundle\Entity\Planilla $estatusPlanilla
     */
    public function removeEstatusPlanilla(\Fgimenez\PlanillaBundle\Entity\Planilla $estatusPlanilla)
    {
        $this->EstatusPlanilla->removeElement($estatusPlanilla);
    }

    /**
     * Get estatusPlanilla
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEstatusPlanilla()
    {
        return $this->EstatusPlanilla;
    }
}
