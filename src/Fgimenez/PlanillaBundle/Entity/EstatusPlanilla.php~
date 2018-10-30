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
class EstatusPlanilla 
{
  
    /**
     * @var integer
     */
    private $id;

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
     * @var integer
     */
    private $id_documento;

    /**
     * @var integer
     */
    private $id_estatus;


    /**
     * @var \Fgimenez\PlanillaBundle\Entity\Planilla
     */
    private $planilla;

    /**
     * @var \Fgimenez\PlanillaBundle\Entity\Estatus
     */
    private $estatus;


    /**
     * Set idDocumento
     *
     * @param integer $idDocumento
     *
     * @return EstatusPlanilla
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
     * Set idEstatus
     *
     * @param integer $idEstatus
     *
     * @return EstatusPlanilla
     */
    public function setIdEstatus($idEstatus)
    {
        $this->id_estatus = $idEstatus;

        return $this;
    }

    /**
     * Get idEstatus
     *
     * @return integer
     */
    public function getIdEstatus()
    {
        return $this->id_estatus;
    }




    /**
     * Set planilla
     *
     * @param \Fgimenez\PlanillaBundle\Entity\Planilla $planilla
     *
     * @return EstatusPlanilla
     */
    public function setPlanilla(\Fgimenez\PlanillaBundle\Entity\Planilla $planilla = null)
    {
        $this->planilla = $planilla;

        return $this;
    }

    /**
     * Get planilla
     *
     * @return \Fgimenez\PlanillaBundle\Entity\Planilla
     */
    public function getPlanilla()
    {
        return $this->planilla;
    }

    /**
     * Set estatus
     *
     * @param \Fgimenez\PlanillaBundle\Entity\Estatus $estatus
     *
     * @return EstatusPlanilla
     */
    public function setEstatus(\Fgimenez\PlanillaBundle\Entity\Estatus $estatus = null)
    {
        $this->estatus = $estatus;

        return $this;
    }

    /**
     * Get estatus
     *
     * @return \Fgimenez\PlanillaBundle\Entity\Estatus
     */
    public function getEstatus()
    {
        return $this->estatus;
    }
    
    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;


    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return EstatusPlanilla
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }
    
    
    /**
     * @var string
     */
    private $AnalistaAsignado;


    /**
     * Set analistaAsignado
     *
     * @param string $analistaAsignado
     *
     * @return EstatusPlanilla
     */
    public function setAnalistaAsignado($analistaAsignado)
    {
        $this->AnalistaAsignado = $analistaAsignado;

        return $this;
    }

    /**
     * Get analistaAsignado
     *
     * @return string
     */
    public function getAnalistaAsignado()
    {
        return $this->AnalistaAsignado;
    }
}
