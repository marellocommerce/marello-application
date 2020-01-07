<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Provider\DayOfWeekProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DayOfWeekType extends AbstractType
{
    protected $dayOfWeekProvider;

    public function __construct(DayOfWeekProvider $dayOfWeekProvider)
    {
        $this->dayOfWeekProvider = $dayOfWeekProvider;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choices', $this->getChoices());
    }

    protected function getChoices()
    {
        return $this->dayOfWeekProvider->getDaysOfWeekChoices();
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
