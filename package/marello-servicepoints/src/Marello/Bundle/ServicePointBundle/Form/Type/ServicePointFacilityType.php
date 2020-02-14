<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Marello\Bundle\ServicePointBundle\Entity\ServicePointFacility;

class ServicePointFacilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('facility', FacilitySelectType::class, [
                'label' => 'marello.servicepoint.servicepoint_facility.facility.label',
                'required' => true
            ])
            ->add('phone', TextType::class, [
                'label' => 'marello.servicepoint.servicepoint_facility.phone.label',
                'required' => false
            ])
            ->add('email', EmailType::class, [
                'label' => 'marello.servicepoint.servicepoint_facility.email.label',
                'required' => false
            ])
            ->add('businessHours', BusinessHoursCollectionType::class, [
                'label' => 'marello.servicepoint.servicepoint_facility.business_hours.label',
            ])
            ->add('businessHoursOverrides', BusinessHoursOverrideCollectionType::class, [
                'label' => 'marello.servicepoint.servicepoint_facility.business_hours_overrides.label'
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
