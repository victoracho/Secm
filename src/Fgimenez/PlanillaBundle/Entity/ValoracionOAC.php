<?php

namespace Fgimenez\PlanillaBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Timestampable;

/**
 * ValoracionOAC
 */
class ValoracionOAC {

    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaEscrito;

    /**
     * @var string
     */
    private $nombreQuienSuscribe;

    /**
     * @var string
     */
    private $condicionQuienSuscribe;

    /**
     * @var string
     */
    private $numeroOficio;

    /**
     * @var int
     */
    private $enteOrgano;

    /**
     * @var int
     */
    private $referenciaNormativa;

    /**
     * @var int
     *
     * @Assert\Length(
     *      max = 3
     * )
     */
    private $articulo;

    /**
     * @var string
     *
     * @Assert\Length(
     *      max = 3
     * )
     */
    private $numeral;

    /**
     * @var string
     *
     * @Assert\Length(
     *      max = 3
     * )
     */
    private $literal;

    /**
     * @var int
     */
    private $competencia;

    /**
     * @var string
     */
    private $usuarioCreacion;

    /**
     * @var \DateTime
     */
    private $fechaCreacion;

    /**
     * @var string
     */
    private $usuarioModificacion;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set fechaEscrito
     *
     * @param \DateTime $fechaEscrito
     *
     * @return ValoracionOAC
     */
    public function setFechaEscrito($fechaEscrito) {
        $this->fechaEscrito = $fechaEscrito;

        return $this;
    }

    /**
     * Get fechaEscrito
     *
     * @return \DateTime
     */
    public function getFechaEscrito() {
        return $this->fechaEscrito;
    }

    /**
     * Set nombreQuienSuscribe
     *
     * @param string $nombreQuienSuscribe
     *
     * @return ValoracionOAC
     */
    public function setNombreQuienSuscribe($nombreQuienSuscribe) {
        $this->nombreQuienSuscribe = $nombreQuienSuscribe;

        return $this;
    }

    /**
     * Get nombreQuienSuscribe
     *
     * @return string
     */
    public function getNombreQuienSuscribe() {
        return $this->nombreQuienSuscribe;
    }

    /**
     * Set condicionQuienSuscribe
     *
     * @param string $condicionQuienSuscribe
     *
     * @return ValoracionOAC
     */
    public function setCondicionQuienSuscribe($condicionQuienSuscribe) {
        $this->condicionQuienSuscribe = $condicionQuienSuscribe;

        return $this;
    }

    /**
     * Get condicionQuienSuscribe
     *
     * @return string
     */
    public function getCondicionQuienSuscribe() {
        return $this->condicionQuienSuscribe;
    }

    /**
     * Set numeroOficio
     *
     * @param string $numeroOficio
     *
     * @return ValoracionOAC
     */
    public function setNumeroOficio($numeroOficio) {
        $this->numeroOficio = $numeroOficio;

        return $this;
    }

    /**
     * Get numeroOficio
     *
     * @return string
     */
    public function getNumeroOficio() {
        return $this->numeroOficio;
    }

    /**
     * Set enteOrgano
     *
     * @param integer $enteOrgano
     *
     * @return ValoracionOAC
     */
    public function setEnteOrgano($enteOrgano) {
        $this->enteOrgano = $enteOrgano;

        return $this;
    }

    /**
     * Get enteOrgano
     *
     * @return int
     */
    public function getEnteOrgano() {
        return $this->enteOrgano;
    }

    /**
     * Set referenciaNormativa
     *
     * @param integer $referenciaNormativa
     *
     * @return ValoracionOAC
     */
    public function setReferenciaNormativa($referenciaNormativa) {
        $this->referenciaNormativa = $referenciaNormativa;

        return $this;
    }

    /**
     * Get referenciaNormativa
     *
     * @return int
     */
    public function getReferenciaNormativa() {
        return $this->referenciaNormativa;
    }

    /**
     * Set articulo
     *
     * @param integer $articulo
     *
     * @return ValoracionOAC
     */
    public function setArticulo($articulo) {
        $this->articulo = $articulo;

        return $this;
    }

    /**
     * Get articulo
     *
     * @return int
     */
    public function getArticulo() {
        return $this->articulo;
    }

    /**
     * Set numeral
     *
     * @param string $numeral
     *
     * @return ValoracionOAC
     */
    public function setNumeral($numeral) {
        $this->numeral = $numeral;

        return $this;
    }

    /**
     * Get numeral
     *
     * @return string
     */
    public function getNumeral() {
        return $this->numeral;
    }

    /**
     * Set literal
     *
     * @param string $literal
     *
     * @return ValoracionOAC
     */
    public function setLiteral($literal) {
        $this->literal = $literal;

        return $this;
    }

    /**
     * Get literal
     *
     * @return string
     */
    public function getLiteral() {
        return $this->literal;
    }

    /**
     * Set competencia
     *
     * @param integer $competencia
     *
     * @return ValoracionOAC
     */
    public function setCompetencia($competencia) {
        $this->competencia = $competencia;

        return $this;
    }

    /**
     * Get competencia
     *
     * @return int
     */
    public function getCompetencia() {
        return $this->competencia;
    }

    /**
     * Set usuarioCreacion
     *
     * @param string $usuarioCreacion
     *
     * @return ValoracionOAC
     */
    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;

        return $this;
    }

    /**
     * Get usuarioCreacion
     *
     * @return string
     */
    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return ValoracionOAC
     */
    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    /**
     * Set usuarioModificacion
     *
     * @param string $usuarioModificacion
     *
     * @return ValoracionOAC
     */
    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;

        return $this;
    }

    /**
     * Get usuarioModificacion
     *
     * @return string
     */
    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     *
     * @return ValoracionOAC
     */
    public function setFechaModificacion($fechaModificacion) {
        $this->fechaModificacion = $fechaModificacion;

        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime
     */
    public function getFechaModificacion() {
        return $this->fechaModificacion;
    }

    /**
     * @var string
     */
    private $observaciones;

    /**
     * Set observaciones
     *
     * @param string $observaciones
     *
     * @return ValoracionOAC
     */
    public function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string
     */
    public function getObservaciones() {
        return $this->observaciones;
    }

    /**
     * @var \Fgimenez\PlanillaBundle\Entity\Planilla
     */
    private $valoracionOAC_planilla;

    /**
     * Set valoracionOACPlanilla
     *
     * @param \Fgimenez\PlanillaBundle\Entity\Planilla $valoracionOACPlanilla
     *
     * @return ValoracionOAC
     */
    public function setValoracionOACPlanilla(\Fgimenez\PlanillaBundle\Entity\Planilla $valoracionOACPlanilla = null) {
        $this->valoracionOAC_planilla = $valoracionOACPlanilla;

        return $this;
    }

    /**
     * Get valoracionOACPlanilla
     *
     * @return \Fgimenez\PlanillaBundle\Entity\Planilla
     */
    public function getValoracionOACPlanilla() {
        return $this->valoracionOAC_planilla;
    }

    /**
     * @var int
     */
    private $organismo_competencia;

    /**
     * Set organismo_competencia
     *
     * @param int $organismo_competencia
     *
     * @return ValoracionOAC
     */
    public function setOrganismoCompetencia($organismo_competencia) {
        $this->organismo_competencia = $organismo_competencia;

        return $this;
    }

    /**
     * Get organismoCompetencia
     *
     * @return integer
     */
    public function getOrganismoCompetencia() {
        return $this->organismo_competencia;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $valoracionOAC_referenciasNormativas;

    /**
     * Constructor
     */
    public function __construct() {
        $this->valoracionOAC_referenciasNormativas = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add valoracionOACReferenciasNormativa
     *
     * @param \Fgimenez\PlanillaBundle\Entity\ValoracionReferenciasNormativas $valoracionOACReferenciasNormativa
     *
     * @return ValoracionOAC
     */
    public function addValoracionOACReferenciasNormativa(\Fgimenez\PlanillaBundle\Entity\ValoracionReferenciasNormativas $valoracionOACReferenciasNormativa) {
        $this->valoracionOAC_referenciasNormativas[] = $valoracionOACReferenciasNormativa;

        return $this;
    }

    /**
     * Remove valoracionOACReferenciasNormativa
     *
     * @param \Fgimenez\PlanillaBundle\Entity\ValoracionReferenciasNormativas $valoracionOACReferenciasNormativa
     */
    public function removeValoracionOACReferenciasNormativa(\Fgimenez\PlanillaBundle\Entity\ValoracionReferenciasNormativas $valoracionOACReferenciasNormativa) {
        $this->valoracionOAC_referenciasNormativas->removeElement($valoracionOACReferenciasNormativa);
    }

    /**
     * Get valoracionOACReferenciasNormativas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getValoracionOACReferenciasNormativas() {
        return $this->valoracionOAC_referenciasNormativas;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreacion() {

        $this->fechaCreacion = new \DateTime();

        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setModificacion() {
        $this->fechaModificacion = new \DateTime();
        return $this;
    }

    /**
     * @var string
     */
    private $correcciones;


    /**
     * Set correcciones
     *
     * @param string $correcciones
     *
     * @return ValoracionOAC
     */
    public function setCorrecciones($correcciones)
    {
        $this->correcciones = $correcciones;

        return $this;
    }

    /**
     * Get correcciones
     *
     * @return string
     */
    public function getCorrecciones()
    {
        return $this->correcciones;
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
     * @return Planilla
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
    /**
     * @var string
     */
    private $asuntoRemision;

    /**
     * @var string
     */
    private $observacion_remision;


    /**
     * Set asuntoRemision
     *
     * @param string $asuntoRemision
     *
     * @return ValoracionOAC
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
     * @return ValoracionOAC
     */
    public function setObservacionRemision($observacionRemision)
    {
        $this->observacion_remision = $observacionRemision;

        return $this;
    }

    /**
     * Get observacionRemision
     *
     * @return string
     */
    public function getObservacionRemision()
    {
        return $this->observacion_remision;
    }


    /**
     * @var array
     */
    private $instrucciones;


    /**
     * Set instrucciones
     *
     * @param array $instrucciones
     *
     * @return ValoracionOAC
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
     * @var string
     */
    private $AnalistaRemision;


    /**
     * Set analistaRemision
     *
     * @param string $analistaRemision
     *
     * @return ValoracionOAC
     */
    public function setAnalistaRemision($analistaRemision)
    {
        $this->AnalistaRemision = $analistaRemision;

        return $this;
    }

    /**
     * Get analistaRemision
     *
     * @return string
     */
    public function getAnalistaRemision()
    {
        return $this->AnalistaRemision;
    }
    /**
     * @var string
     */
    private $OficioRemision;


    /**
     * Set oficioRemision
     *
     * @param string $oficioRemision
     *
     * @return ValoracionOAC
     */
    public function setOficioRemision($oficioRemision)
    {
        $this->OficioRemision = $oficioRemision;

        return $this;
    }

    /**
     * Get oficioRemision
     *
     * @return string
     */
    public function getOficioRemision()
    {
        return $this->OficioRemision;
    }
    /**
     * @var \DateTime
     */
    private $fecha_remision;

    /**
     * @var \DateTime
     */
    private $fecha_nota_remision;


    /**
     * Set fechaRemision
     *
     * @param \DateTime $fechaRemision
     *
     * @return ValoracionOAC
     */
    public function setFechaRemision($fechaRemision)
    {
        $this->fecha_remision = $fechaRemision;

        return $this;
    }

    /**
     * Get fechaRemision
     *
     * @return \DateTime
     */
    public function getFechaRemision()
    {
        return $this->fecha_remision;
    }

    /**
     * Set fechaNotaRemision
     *
     * @param \DateTime $fechaNotaRemision
     *
     * @return ValoracionOAC
     */
    public function setFechaNotaRemision($fechaNotaRemision)
    {
        $this->fecha_nota_remision = $fechaNotaRemision;

        return $this;
    }

    /**
     * Get fechaNotaRemision
     *
     * @return \DateTime
     */
    public function getFechaNotaRemision()
    {
        return $this->fecha_nota_remision;
    }
}
