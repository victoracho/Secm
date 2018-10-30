<?php

namespace Fgimenez\PlanillaBundle\Form;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;


class AsignarFilterType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('codigo', Filters\TextFilterType::class)
                
                ->add('ultimo_status', Filters\EntityFilterType::class, array(
                    'class' => 'Fgimenez\PlanillaBundle\Entity\Estatus',
                    'choice_label' => 'nombre',
                    'label' => 'Último Estatus',
                ))
                /*->add('tipo_caso', Filters\EntityFilterType::class, array(
                    'class' => 'Fgimenez\PlanillaBundle\Entity\TipoCaso',
                    'choice_label' => 'nombre',
                    'label' => 'Tipo Caso',
                ))*/
                ->add('tipo_atencion', Filters\EntityFilterType::class, array(
                    'class' => 'Mmartin4\MantenimientoBundle\Entity\Mecanismo_Presentacion',
                    'choice_label' => 'nombre',
                    'label' => 'Mecanismo Presentacion',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('e')
                                ->where("e.estatus=1")
                                ->orderby("e.id");
                    },
                ))
                
        ;

        $builder->setMethod("GET");
    }

    public function getBlockPrefix() {
        return null;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ));
    }

}
