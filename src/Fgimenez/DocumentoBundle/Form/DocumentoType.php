<?php

namespace Fgimenez\DocumentoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DocumentoType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                /*->add('tipoSoporte', ChoiceType::class, array(
                    'label' => 'Tipo Soporte',
                    'choices' => array(
                        'Físico' => 'F',
                        'Digital' => 'D'),
                    'placeholder' => 'Seleccione'
                ))*/
                ->add('tipo_documento', EntityType::class, array(
                    'class' => 'Fgimenez\DocumentoBundle\Entity\TipoDocumento',
                    'choice_label' => 'nombre',
                    'label' => 'Tipo Soporte',
                    'placeholder' => 'Seleccione',
                    'empty_data' => null,
                    'required' => false
                ))
                ->add('tipo_documento_text', HiddenType::class, array('mapped' => false))
                
                ->add('ruta', FileType::class, array(
                    //"label" => "Imagen:",
                    "attr" => array("class" => "form-control"),
                    "data_class" => null,
                    'required' => false
                ))

                ->add('ruta2', HiddenType::class, array('mapped' => false))
                
                /*->add('resumen', CKEditorType::class, array(
                    // 'config_name' => 'my_config',
                    'required' => false
                ))*/
                
                ->add('Nombre', TextType::class, array(
                    // 'config_name' => 'my_config',
                    'required' => true
                ))
                
                ->add('resumen', TextType::class, array(
                    // 'config_name' => 'my_config',
                    'required' => true
                ))
                
                
        //->add('status')
        //->add('acceso')

        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Fgimenez\DocumentoBundle\Entity\Documento'
        ));
    }

}
