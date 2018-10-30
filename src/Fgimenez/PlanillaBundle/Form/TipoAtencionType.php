<?php

namespace Fgimenez\PlanillaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TipoAtencionType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('nombre')
                ->add('descripcion', CKEditorType::class, array(
                    // 'config_name' => 'my_config',
                    'required' => false,
                    'label' => 'Descripción'
                ))
                ->add('status', ChoiceType::class, array(
                    'choices' => array(
                        'Activo' => 1,
                        'Inactivo' => 0
                    ),
                    'label' => 'Estatus'
                ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Fgimenez\PlanillaBundle\Entity\TipoAtencion'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'fgimenez_planillabundle_tipoatencion';
    }

}
