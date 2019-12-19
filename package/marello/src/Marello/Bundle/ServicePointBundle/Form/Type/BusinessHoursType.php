<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\BusinessHours;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessHoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dayOfWeek', DayOfWeekType::class, [
                'label' => 'marello.servicepoint.businesshours.day_of_week.label',
                'required' => true,
            ])
            ->add('timePeriods', CollectionType::class, [
                'label' => 'marello.servicepoint.businesshours.time_periods.label',
                'entry_type' => TimePeriodType::class,
                'prototype_name' => '__timeperiods_name__',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class' => BusinessHours::class,
        ]);
    }
}
