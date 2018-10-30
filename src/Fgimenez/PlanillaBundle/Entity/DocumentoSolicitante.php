<?php

namespace Fgimenez\PlanillaBundle\Entity;

/**
 * documento_solicitante
 */
class DocumentoSolicitante
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $idPlanilla;

    /**
     * @var int
     */
    private $idPersona;

    /**
     * @var string
     */
    private $usuarioCreacion;

    /**
     * @var \DateTime
     */
    private $fechaCreacion;


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
     * Set idPlanilla
     *
     * @param integer $idPlanilla
     *
     * @return documento_solicitante
     */
    public function setIdPlanilla($idPlanilla)
    {
        $this->idPlanilla = $idPlanilla;

        return $this;
    }

    /**
     * Get idPlanilla
     *
     * @return int
     */
    public function getIdPlanilla()
    {
        return $this->idPlanilla;
    }

    /**
     * Set idPersona
     *
     * @param integer $idPersona
     *
     * @return documento_solicitante
     */
    public function setIdPersona($idPersona)
    {
        $this->idPersona = $idPersona;

        return $this;
    }

    /**
     * Get idPersona
     *
     * @return int
     */
    public function getIdPersona()
    {
        return $this->idPersona;
    }

    /**
     * Set usuarioCreacion
     *
     * @param string $usuarioCreacion
     *
     * @return documento_solicitante
     */
    public function setUsuarioCreacion($usuarioCreacion)
    {
        $this->usuarioCreacion = $usuarioCreacion;

        return $this;
    }

    /**
     * Get usuarioCreacion
     *
     * @return string
     */
    public function getUsuarioCreacion()
    {
        return $this->usuarioCreacion;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return documento_solicitante
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }
    /**
     * @var \Fgimenez\PlanillaBundle\Entity\Planilla
     */
    private $planilla;

    /**
     * @var \jsanchez\DatosPersonalesBundle\Entity\persona
     */
    private $persona;


    /**
     * Set planilla
     *
     * @param \Fgimenez\PlanillaBundle\Entity\Planilla $planilla
     *
     * @return DocumentoSolicitante
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
     * Set persona
     *
     * @param \jsanchez\DatosPersonalesBundle\Entity\persona $persona
     *
     * @return DocumentoSolicitante
     */
    public function setPersona(\jsanchez\DatosPersonalesBundle\Entity\persona $persona = null)
    {
        $this->persona = $persona;

        return $this;
    }

    /**
     * Get persona
     *
     * @return \jsanchez\DatosPersonalesBundle\Entity\persona
     */
    public function getPersona()
    {
        return $this->persona;
    }

    /**
     * @ORM\PrePersist
     */
    public function setFechaCreacionValue()
    {
        $this->fechaCreacion = new \DateTime(); 
    }
}
