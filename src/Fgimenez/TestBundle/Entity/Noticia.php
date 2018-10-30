<?php

namespace Fgimenez\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * Noticia
 * @UniqueEntity("slug")
 */
class Noticia
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string   
     * @Assert\Length(
     *      min = 10,
     *      max = 200,
     *      minMessage = "El titulo debe ser {{ limit }} caracteres minimo",
     *      maxMessage = "El titulo no debe ser mas de {{ limit }} caracteres"
     * )
     */
    private $titulo;

    /**
     * @var string
     */
    private $contenido;

    /**
     * @var string
     */
    private $slug;


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
     * Set titulo
     *
     * @param string $titulo
     *
     * @return Noticia
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get titulo
     *
     * @return string
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set contenido
     *
     * @param string $contenido
     *
     * @return Noticia
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;

        return $this;
    }

    /**
     * Get contenido
     *
     * @return string
     */
    public function getContenido()
    {
        return $this->contenido;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Noticia
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
    /**
     * @var boolean
     */
    private $publicado;


    /**
     * Set publicado
     *
     * @param boolean $publicado
     *
     * @return Noticia
     */
    public function setPublicado($publicado)
    {
        $this->publicado = $publicado;

        return $this;
    }

    /**
     * Get publicado
     *
     * @return boolean
     */
    public function getPublicado()
    {
        return $this->publicado;
    }
    /**
     * @var integer
     */
    private $categoria_id;

    /**
     * @var \Fgimenez\TestBundle\Entity\Categoria
     */
    private $categoria;


    /**
     * Set categoriaId
     *
     * @param integer $categoriaId
     *
     * @return Noticia
     */
    public function setCategoriaId($categoriaId)
    {
        $this->categoria_id = $categoriaId;

        return $this;
    }

    /**
     * Get categoriaId
     *
     * @return integer
     */
    public function getCategoriaId()
    {
        return $this->categoria_id;
    }

    /**
     * Set categoria
     *
     * @param \Fgimenez\TestBundle\Entity\Categoria $categoria
     *
     * @return Noticia
     */
    public function setCategoria(\Fgimenez\TestBundle\Entity\Categoria $categoria = null)
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * Get categoria
     *
     * @return \Fgimenez\TestBundle\Entity\Categoria
     */
    public function getCategoria()
    {
        return $this->categoria;
    }
  
    /**
     * @var string
     * @Assert\Image(
     *     minWidth = 300,
     *     maxWidth = 800,
     *     minHeight = 100,
     *     maxHeight = 300,
     *     maxSize = "1024k",
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "Por favor suba un archivo valido (png / jpg)"
     * )
     */
    private $imagen;


    /**
     * Set imagen
     *
     * @param string $imagen
     *
     * @return Noticia
     */
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;

        return $this;
    }

    /**
     * Get imagen
     *
     * @return string
     */
    public function getImagen()
    {
        return $this->imagen;
    }
      /**
     * @var string   
     * @Assert\Length(
     *      min = 10,
     *      max = 255,
     *      minMessage = "El titulo debe ser {{ limit }} caracteres minimo",
     *      maxMessage = "El titulo no debe ser mas de {{ limit }} caracteres"
     * )
     */
    private $antetitulo;


    /**
     * Set antetitulo
     *
     * @param string $antetitulo
     *
     * @return Noticia
     */
    public function setAntetitulo($antetitulo)
    {
        $this->antetitulo = $antetitulo;

        return $this;
    }

    /**
     * Get antetitulo
     *
     * @return string
     */
    public function getAntetitulo()
    {
        return $this->antetitulo;
    }
    
    
    /**
     * @var string
     */
    private $resumen;

    /**
     * @var \DateTime
     */
    private $fecha;


    /**
     * Set resumen
     *
     * @param string $resumen
     *
     * @return Noticia
     */
    public function setResumen($resumen)
    {
        $this->resumen = $resumen;

        return $this;
    }

    /**
     * Get resumen
     *
     * @return string
     */
    public function getResumen()
    {
        return $this->resumen;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return Noticia
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
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
     */
    private $locale;
    
    
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
    
}
