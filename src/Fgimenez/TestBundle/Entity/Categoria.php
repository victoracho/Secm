<?php

namespace Fgimenez\TestBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
/**
 * Categoria
 * @ORM\Entity
 * @UniqueEntity("slug")
 * @UniqueEntity("titulo")
 *
 */
class Categoria
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string   
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "El titulo debe ser {{ limit }} caracteres minimo",
     *      maxMessage = "El titulo no debe ser mas de {{ limit }} caracteres"
     * )
     */
    private $titulo;

    /**
     * @var string
     */
    private $descripcion;

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
     * @return Categoria
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
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Categoria
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Categoria
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
     * @var string
     *
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 400,
     *     minHeight = 200,
     *     maxHeight = 400,
     *     maxSize = "1024k",
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "Por favor suba un archivo valido (png / jpg)"
     * )
     */
    private $imagen ;


    /**
     * Set imagen
     *
     * @param string $imagen
     *
     * @return Categoria
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
     * @var boolean
     */
    private $publicado;


 

    /**
     * Set publicado
     *
     * @param boolean $publicado
     *
     * @return Categoria
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $noticias;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->noticias = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add noticia
     *
     * @param \Fgimenez\TestBundle\Entity\Noticia $noticia
     *
     * @return Categoria
     */
    public function addNoticia(\Fgimenez\TestBundle\Entity\Noticia $noticia)
    {
        $this->noticias[] = $noticia;

        return $this;
    }

    /**
     * Remove noticia
     *
     * @param \Fgimenez\TestBundle\Entity\Noticia $noticia
     */
    public function removeNoticia(\Fgimenez\TestBundle\Entity\Noticia $noticia)
    {
        $this->noticias->removeElement($noticia);
    }

    /**
     * Get noticias
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNoticias()
    {
        return $this->noticias;
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