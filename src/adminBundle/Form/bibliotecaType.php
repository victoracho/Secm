<?php

namespace adminBundle\Form;
use adminBundle\Entity\biblioteca;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
class bibliotecaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('descripcion')
            ->add('fecha')
            ->add('activo')
            ->add('brochure', FileType::class,  array('label' => 'Archivo (tipo PDF)','data_class' => null))

            ->add('pdf2', HiddenType::class, array(
                  'data' => 'abcdef',
                  'mapped' => false,
                ))

            ->add('biblioteca_categoria', EntityType::class, array(
                'class' => 'adminBundle\Entity\biblioteca_categoria',
                'choice_label' => 'nombre',
                'placeholder' => 'Por favor escoge',
                'empty_data' => null,
                'required' => false

            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'adminBundle\Entity\biblioteca'
        ));
    }
}
