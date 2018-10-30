<?php

namespace Fgimenez\PlanillaBundle\Entity;

/**
 * Naturaleza
 */
class Naturaleza
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var integer
     */
    private $tipoRequerimiento;


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
     * @return Naturaleza
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
     * Set tipoRequerimiento
     *
     * @param integer $tipoRequerimiento
     *
     * @return Naturaleza
     */
    public function setTipoRequerimiento($tipoRequerimiento)
    {
        $this->tipoRequerimiento = $tipoRequerimiento;

        return $this;
    }

    /**
     * Get tipoRequerimiento
     *
     * @return integer
     */
    public function getTipoRequerimiento()
    {
        return $this->tipoRequerimiento;
    }
}
