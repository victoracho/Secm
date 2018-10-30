<?php

namespace Fgimenez\TestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CategoriaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('locale', ChoiceType::class, array(
                    'mapped' => false,
                    'choices' => array(
                        'Español' => 'es',
                        'Ingles' => 'en',
                    )
                ))
                ->add('titulo')
                ->add('descripcion', CKEditorType::class, array(
                    // 'config_name' => 'my_config',
                    'required' => false
                ))
                ->add('imagen', FileType::class, array(
                    //"label" => "Imagen:",
                    "attr" => array("class" => "form-control"),
                    "data_class" => null,
                    'required' => false
                ))
                ->add('imagen2', HiddenType::class, array('mapped' => false))
                ->add('slug')
                ->add('publicado')
        /* ->add('noticias', EntityType::class, array(
          'class' => 'Fgimenez\TestBundle\Entity\Noticia',
          'choice_label' => 'titulo',

          'required' => false,
          'expanded' => false,
          'multiple' => true
          )) */
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Fgimenez\TestBundle\Entity\Categoria'
        ));
    }

}
