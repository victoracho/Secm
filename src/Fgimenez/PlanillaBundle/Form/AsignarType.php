<?php

namespace Fgimenez\PlanillaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class AsignarType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('motivo_reasignacion', EntityType::class, array(
                    'class' => 'Mmartin4\MantenimientoBundle\Entity\motivo_reasignacion',
                    'label' => 'Motivo Reasignacioón',
                    'choice_label' => 'nombre',
                    'placeholder' => 'Seleccione',
                    'empty_data' => null,
                    'required' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('e')
                                ->where("e.estatus=1")
                                ->orderby("e.id");
                    },
        ));
    }

}
