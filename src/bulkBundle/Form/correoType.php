<?php

namespace bulkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
class correoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fecha')
            ->add('fechaEdicion')
            
            ->add('asunto', TextType::class, array(
              'mapped' => false
            ))
            ->add('texto', TextareaType::class, array(
              'mapped' => false,
              'attr' => array(
              'class' => 'ckeditor',
               // Skip it if you want to use default theme
          )

      ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'bulkBundle\Entity\correo'
        ));
    }
}
