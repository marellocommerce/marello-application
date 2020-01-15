<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\BusinessHoursOverride;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessHoursOverrideType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_servicepoint_business_hours_override';

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
            ->add('timePeriods', TimePeriodOverrideCollectionType::class, [
                'label' => 'marello.servicepoint.businesshours.time_periods.label'
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

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
