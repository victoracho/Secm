<?php

namespace Fgimenez\PlanillaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
class ValoracionOACType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('nombreQuienSuscribe', TextType::class, array(
                  'required' => true ))

                 ->add('condicionQuienSuscribe')

                ->add('fechaEscrito', DateType::class, array(
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    // do not render as type="date", to avoid HTML5 date pickers
                    'html5' => false,
                    'required' => true,
                    'input' => 'datetime',
                        // add a class that can be selected in JavaScript
                        //'attr' => ['class' => 'js-datepicker'],
                ))


                ->add('numeroOficio')

                ->add('enteOrgano', TextType::class, array(
                  'required' => false,
                  'mapped'=>false ))

                  ->add('id_ente', HiddenType::class, array(
                    'required' => false,
                    'mapped'=> false ))

                ->add('referenciaNormativa', TextType::class, array(
                  'required' => false ))
                ->add('articulo')
                ->add('numeral')
                ->add('literal')
                ->add('competencia', ChoiceType::class, array(
                  'choices' => array(
                   'CGR' => 1,
                   'Ente Externo' => 2),
                   'placeholder' => 'Seleccione',))
                 ->add('organismoCompetencia', TextType::class, array(
                  'required' => true,
                     'mapped'=>false ))
                ->add('observaciones')

        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Fgimenez\PlanillaBundle\Entity\ValoracionOAC'
        ));
    }

}
