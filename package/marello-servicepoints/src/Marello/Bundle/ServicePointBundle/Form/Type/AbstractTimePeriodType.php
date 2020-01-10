<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractTimePeriodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('openTime', TimeType::class, [
                'label' => 'marello.servicepoint.timeperiod.open_time.label',
                'required' => true,
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC',
            ])
            ->add('closeTime', TimeType::class, [
                'label' => 'marello.servicepoint.timeperiod.close_time.label',
                'required' => true,
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->getDataClass(),
        ]);
    }

    abstract protected function getDataClass();
}
