<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\Facility;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class FacilityType extends AbstractType
{
    const NAME = 'marello_servicepoint_facility';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('labels', LocalizedFallbackValueCollectionType::class, [
                'label' => 'marello.servicepoint.facility.labels.label',
                'required' => true,
            ])
            ->add('code', TextType::class, [
                'label' => 'marello.servicepoint.facility.code.label',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Facility::class,
        ]);
    }
}
