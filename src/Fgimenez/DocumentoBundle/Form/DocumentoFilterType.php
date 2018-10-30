<?php

namespace Fgimenez\DocumentoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;


class DocumentoFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', Filters\NumberFilterType::class)
                 ->add('tipo_documento', Filters\EntityFilterType::class, array(
                    'class' => 'Fgimenez\DocumentoBundle\Entity\TipoDocumento',
                    'choice_label' => 'nombre',
            )) 
            //->add('idTipoDocumento', Filters\NumberFilterType::class)
           
            ->add('ruta', Filters\TextFilterType::class)
            ->add('resumen', Filters\TextFilterType::class)
            ->add('status', Filters\BooleanFilterType::class)
            ->add('acceso', Filters\TextFilterType::class)
        
           
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
