<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\ServicePointFacility;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServicePointFacilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('facility', FacilitySelectType::class, [
                'label' => 'marello.servicepoint.servicepoint_facility.facility.label',
                'required' => true,
            ])
            ->add('phone', TextType::class, [
                'label' => 'marello.servicepoint.servicepoint_facility.phone.label',
            ])
            ->add('email', EmailType::class, [
                'label' => 'marello.servicepoint.servicepoint_facility.email.label',
            ])
            ->add('businessHours', BusinessHoursCollectionType::class, [
                'label' => 'marello.servicepoint.servicepoint_facility.business_hours.label',
            ])
            ->add('businessHoursOverrides', CollectionType::class, [
                'label' => 'marello.servicepoint.servicepoint_facility.business_hours_overrides.label',
                'entry_type' => BusinessHoursOverrideType::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ServicePointFacility::class,
        ]);
    }
}