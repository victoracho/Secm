<?php

namespace adminBundle\Entity;

/**
 * biblioteca_categoria
 */
class biblioteca_categoria
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var int
     */
    private $activo;


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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return biblioteca_categoria
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
     * Set activo
     *
     * @param integer $activo
     *
     * @return biblioteca_categoria
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return int
     */
    public function getActivo()
    {
        return $this->activo;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $biblioteca;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->biblioteca = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add biblioteca
     *
     * @param \adminBundle\Entity\biblioteca $biblioteca
     *
     * @return biblioteca_categoria
     */
    public function addBiblioteca(\adminBundle\Entity\biblioteca $biblioteca)
    {
        $this->biblioteca[] = $biblioteca;

        return $this;
    }

    /**
     * Remove biblioteca
     *
     * @param \adminBundle\Entity\biblioteca $biblioteca
     */
    public function removeBiblioteca(\adminBundle\Entity\biblioteca $biblioteca)
    {
        $this->biblioteca->removeElement($biblioteca);
    }

    /**
     * Get biblioteca
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBiblioteca()
    {
        return $this->biblioteca;
    }


    public function __toString() {
    return $this->nombre;
}

}
