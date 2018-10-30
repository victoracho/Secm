<?php

namespace adminBundle\Entity;

/**
 * biblioteca
 */
class biblioteca
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
     * @var string
     */
    private $descripcion;

    /**
     * @var \DateTime
     */
    private $fecha;



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
     * @return biblioteca
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
     * @return biblioteca
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
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return biblioteca
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }




    /**
     * Set activo
     *
     * @param integer $activo
     *
     * @return biblioteca
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
     * @var string
     */
    private $brochure;

    public function getBrochure()
    {
        return $this->brochure;
    }

    public function setBrochure($brochure)
    {
        $this->brochure = $brochure;

        return $this;
    }
    /**
     * @var \adminBundle\Entity\biblioteca_categoria
     */
    private $biblioteca_categoria;


    /**
     * Set bibliotecaCategoria
     *
     * @param \adminBundle\Entity\biblioteca_categoria $bibliotecaCategoria
     *
     * @return biblioteca
     */
    public function setBiblioteca_categoria(\adminBundle\Entity\biblioteca_categoria $bibliotecaCategoria = null)
    {
        $this->biblioteca_categoria = $bibliotecaCategoria;

        return $this;
    }

    /**
     * Get bibliotecaCategoria
     *
     * @return \adminBundle\Entity\biblioteca_categoria
     */
    public function getBiblioteca_categoria()
    {
        return $this->biblioteca_categoria;
    }



        /**
         * Set bibliotecaCategoria
         *
         * @param \adminBundle\Entity\biblioteca_categoria $bibliotecaCategoria
         *
         * @return biblioteca
         */
        public function setBibliotecaCategoria(\adminBundle\Entity\biblioteca_categoria $bibliotecaCategoria = null)
        {
            $this->biblioteca_categoria = $bibliotecaCategoria;

            return $this;
        }

        /**
         * Get bibliotecaCategoria
         *
         * @return \adminBundle\Entity\biblioteca_categoria
         */
        public function getBibliotecaCategoria()
        {
            return $this->biblioteca_categoria;
        }
}
