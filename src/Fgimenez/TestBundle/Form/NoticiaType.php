<?php

namespace Fgimenez\TestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class NoticiaType extends AbstractType {

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
                ->add('categoria', EntityType::class, array(
                    'class' => 'Fgimenez\TestBundle\Entity\Categoria',
                    'choice_label' => 'titulo',
                    'placeholder' => 'Please choose',
                    'empty_data' => null,
                    'required' => false
                ))
                ->add('fecha', DateType::class, array(
                    'years' => range(date('Y'), intval(date('Y'))-1)
                ))
                ->add('titulo')
                ->add('antetitulo')
                ->add('resumen', CKEditorType::class, array(
                    // 'config_name' => 'my_config',
                    'required' => true
                ))
                ->add('contenido', CKEditorType::class, array(
                    // 'config_name' => 'my_config',
                    'required' => true
                ))
                ->add('slug')
                ->add('imagen', FileType::class, array(
                    //"label" => "Imagen:",
                    "attr" => array("class" => "form-control"),
                    "data_class" => null,
                    'required' => false
                ))
                ->add('imagen2', HiddenType::class, array('mapped' => false))
                ->add('publicado')
        //->add('categoria_id')

        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Fgimenez\TestBundle\Entity\Noticia'
        ));
    }

}