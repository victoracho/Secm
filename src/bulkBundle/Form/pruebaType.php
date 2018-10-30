<?php

namespace bulkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class pruebaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', TextType::class, array(
              'label' => 'Nombre'
            ))

            ->add('fechaNac', DateType::class, array(
                   'widget' => 'single_text',
                   // do not render as type="date", to avoid HTML5 date pickers
                   'html5' => false,
                   'required' => true,
                   'input'=>'string',
                   'mapped' => false,
                    'label' => 'Fecha de Nacimiento'
               ))
               ->add('pais', TextType::class, array(
                 'label' => 'Pais'
               ))

               ->add('telefono', TextType::class, array(
                 'label' => 'Número de Telefono'
               ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'bulkBundle\Entity\Prueba'
        ));
    }
}
