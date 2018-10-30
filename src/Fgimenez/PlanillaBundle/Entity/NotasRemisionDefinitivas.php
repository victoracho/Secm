<?php

namespace Fgimenez\PlanillaBundle\Entity;

/**
 * NotasRemisionDefinitivas
 */
class NotasRemisionDefinitivas
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $asuntoRemision;

    /**
     * @var string
     */
    private $observacionRemision;

    /**
     * @var array
     */
    private $instrucciones;

    /**
     * @var string
     */
    private $analistaRemision;

    /**
     * @var string
     */
    private $oficioRemision;

    /**
     * @var \DateTime
     */
    private $fechaNotaRemision;


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
     * Set asuntoRemision
     *
     * @param string $asuntoRemision
     *
     * @return NotasRemisionDefinitivas
     */
    public function setAsuntoRemision($asuntoRemision)
    {
        $this->asuntoRemision = $asuntoRemision;

        return $this;
    }

    /**
     * Get asuntoRemision
     *
     * @return string
     */
    public function getAsuntoRemision()
    {
        return $this->asuntoRemision;
    }

    /**
     * Set observacionRemision
     *
     * @param string $observacionRemision
     *
     * @return NotasRemisionDefinitivas
     */
    public function setObservacionRemision($observacionRemision)
    {
        $this->observacionRemision = $observacionRemision;

        return $this;
    }

    /**
     * Get observacionRemision
     *
     * @return string
     */
    public function getObservacionRemision()
    {
        return $this->observacionRemision;
    }

    /**
     * Set instrucciones
     *
     * @param array $instrucciones
     *
     * @return NotasRemisionDefinitivas
     */
    public function setInstrucciones($instrucciones)
    {
        $this->instrucciones = $instrucciones;

        return $this;
    }

    /**
     * Get instrucciones
     *
     * @return array
     */
    public function getInstrucciones()
    {
        return $this->instrucciones;
    }

    /**
     * Set analistaRemision
     *
     * @param string $analistaRemision
     *
     * @return NotasRemisionDefinitivas
     */
    public function setAnalistaRemision($analistaRemision)
    {
        $this->analistaRemision = $analistaRemision;

        return $this;
    }

    /**
     * Get analistaRemision
     *
     * @return string
     */
    public function getAnalistaRemision()
    {
        return $this->analistaRemision;
    }

    /**
     * Set oficioRemision
     *
     * @param string $oficioRemision
     *
     * @return NotasRemisionDefinitivas
     */
    public function setOficioRemision($oficioRemision)
    {
        $this->oficioRemision = $oficioRemision;

        return $this;
    }

    /**
     * Get oficioRemision
     *
     * @return string
     */
    public function getOficioRemision()
    {
        return $this->oficioRemision;
    }

    /**
     * Set fechaNotaRemision
     *
     * @param \DateTime $fechaNotaRemision
     *
     * @return NotasRemisionDefinitivas
     */
    public function setFechaNotaRemision($fechaNotaRemision)
    {
        $this->fechaNotaRemision = $fechaNotaRemision;

        return $this;
    }

    /**
     * Get fechaNotaRemision
     *
     * @return \DateTime
     */
    public function getFechaNotaRemision()
    {
        return $this->fechaNotaRemision;
    }
   
    
    /**
     * @var \Fgimenez\PlanillaBundle\Entity\Planilla
     */
    private $notas_remision_planilla;


    /**
     * Set notasRemisionPlanilla
     *
     * @param \Fgimenez\PlanillaBundle\Entity\Planilla $notasRemisionPlanilla
     *
     * @return NotasRemisionDefinitivas
     */
    public function setNotasRemisionPlanilla(\Fgimenez\PlanillaBundle\Entity\Planilla $notasRemisionPlanilla = null)
    {
        $this->notas_remision_planilla = $notasRemisionPlanilla;

        return $this;
    }

    /**
     * Get notasRemisionPlanilla
     *
     * @return \Fgimenez\PlanillaBundle\Entity\Planilla
     */
    public function getNotasRemisionPlanilla()
    {
        return $this->notas_remision_planilla;
    }
}
