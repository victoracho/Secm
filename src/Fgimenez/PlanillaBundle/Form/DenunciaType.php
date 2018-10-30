<?php

namespace Fgimenez\PlanillaBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DenunciaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('tipo_atencion', EntityType::class, array(
                    'class' => 'Mmartin4\MantenimientoBundle\Entity\Mecanismo_Presentacion',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('e')
                                ->where("e.estatus=1");
                    },
                    'label' => 'Mecanismo de Presentación',
                    'choice_label' => 'nombre',
                    'placeholder' => 'Seleccione',
                    'empty_data' => null,
                    'required' => true
                ))
                ->add('codigo', HiddenType::class)

                ->add('hecho',TextareaType::class, array(
                    'attr' => array('class' => 'tinymce'),
                ))

                /* ->add('hecho', CKEditorType::class, array(
                  // 'config_name' => 'my_config',
                  'required' => false
                  )) */
                /*->add('fechaHecho', DateType::class, array(
                     'widget' => 'text',
                    'format' => 'yyyy-MM-d'
                ))*/

               ->add('fechaHecho', DateType::class, array(
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    // do not render as type="date", to avoid HTML5 date pickers
                    'html5' => false,
                    'required' => true,
                    'input'=>'datetime',
                        // add a class that can be selected in JavaScript
                        //'attr' => ['class' => 'js-datepicker'],
                ))



        /* ->add('valoracion', CKEditorType::class, array(
          // 'config_name' => 'my_config',
          'required' => false
          )) */

        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Fgimenez\PlanillaBundle\Entity\Planilla'
        ));
    }

}
