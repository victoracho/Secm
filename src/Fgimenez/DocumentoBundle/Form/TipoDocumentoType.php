<?php

namespace Fgimenez\DocumentoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TipoDocumentoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('descripcion', CKEditorType::class, array(
                    // 'config_name' => 'my_config',
                    'required' => false,
                    'label' => 'Descripción'
                ))
            ->add('status', ChoiceType::class, array(
                'choices' => array(
                  'Activo' => 'A',
                  'Inactivo' => 'I'
                 ),
                'label' => 'Estatus'            
                
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Fgimenez\DocumentoBundle\Entity\TipoDocumento'
        ));
    }
}
