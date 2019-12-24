<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\BusinessHoursOverride;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpenStatusSelectType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => false,
            'expanded' => false,
            'choices' => $this->getChoices(),
        ]);
    }

    protected function getChoices()
    {
        return [
            'marello.servicepoint.businesshours.open_status.open.label' => BusinessHoursOverride::STATUS_OPEN,
            'marello.servicepoint.businesshours.open_status.closed.label' => BusinessHoursOverride::STATUS_CLOSED,
        ];
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
