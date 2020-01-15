<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\BusinessHoursOverride;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessHoursOverrideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', OroDateType::class, [
                'label' => 'marello.servicepoint.businesshours.date.label',
                'required' => true,
            ])
            ->add('openStatus', OpenStatusSelectType::class, [
                'label' => 'marello.servicepoint.businesshours.open_status.label',
                'required' => true,
            ])
            ->add('timePeriods', CollectionType::class, [
                'label' => 'marello.servicepoint.businesshours.time_periods.label',
                'entry_type' => TimePeriodOverrideType::class,
                'prototype_name' => '__timeperiods_name__',
                'row_count_initial' => 0,
                'show_form_when_empty' => false,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @return $this
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class' => BusinessHoursOverride::class,
        ]);
    }
}
