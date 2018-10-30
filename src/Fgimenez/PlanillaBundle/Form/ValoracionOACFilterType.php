<?php

namespace Fgimenez\PlanillaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;


class ValoracionOACFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', Filters\NumberFilterType::class)
            ->add('fechaEscrito', Filters\DateTimeFilterType::class)
            ->add('nombreQuienSuscribe', Filters\TextFilterType::class)
            ->add('condicionQuienSuscribe', Filters\TextFilterType::class)
            ->add('numeroOficio', Filters\TextFilterType::class)
            ->add('enteOrgano', Filters\NumberFilterType::class)
            ->add('referenciaNormativa', Filters\NumberFilterType::class)
            ->add('articulo', Filters\NumberFilterType::class)
            ->add('numeral', Filters\TextFilterType::class)
            ->add('literal', Filters\TextFilterType::class)
            ->add('competencia', Filters\NumberFilterType::class)
            ->add('observaciones', Filters\TextFilterType::class)
            ->add('usuarioCreacion', Filters\TextFilterType::class)
            ->add('fechaCreacion', Filters\DateTimeFilterType::class)
            ->add('usuarioModificacion', Filters\TextFilterType::class)
            ->add('fechaModificacion', Filters\DateTimeFilterType::class)
        
            ->add('valoracionOAC_planilla', Filters\EntityFilterType::class, array(
                    'class' => 'Fgimenez\PlanillaBundle\Entity\Planilla',
                    'choice_label' => 'codigo',
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
