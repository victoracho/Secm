<?php

namespace Fgimenez\TestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;


class NoticiaFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', Filters\NumberFilterType::class)
            ->add('titulo', Filters\TextFilterType::class)
            ->add('antetitulo', Filters\TextFilterType::class)
            ->add('resumen', Filters\TextFilterType::class)
            ->add('contenido', Filters\TextFilterType::class)
            ->add('fecha', Filters\DateFilterType::class)
            ->add('slug', Filters\TextFilterType::class)
            ->add('imagen', Filters\TextFilterType::class)
            ->add('publicado', Filters\BooleanFilterType::class)
            ->add('categoria_id', Filters\NumberFilterType::class)
        
            ->add('categoria', Filters\EntityFilterType::class, array(
                    'class' => 'Fgimenez\TestBundle\Entity\Categoria',
                    'choice_label' => 'titulo',
            )) 
        ;
        $builder->setMethod("GET");


    }

    public function getBlockPrefix()
    {
        return null;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ));
    }
}
