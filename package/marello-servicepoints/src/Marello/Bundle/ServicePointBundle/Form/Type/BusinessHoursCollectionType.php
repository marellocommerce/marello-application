<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Form\EventListener\BusinessHoursCollectionEventListener;
use Marello\Bundle\ServicePointBundle\Provider\DayOfWeekProvider;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessHoursCollectionType extends AbstractType
{
    protected $dayOfWeekProvider;

    public function __construct(DayOfWeekProvider $dayOfWeekProvider)
    {
        $this->dayOfWeekProvider = $dayOfWeekProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new BusinessHoursCollectionEventListener($this->dayOfWeekProvider));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => BusinessHoursType::class,
            'allow_add' => false,
            'allow_add_after' => false,
            'allow_remove' => false,
        ]);
    }

    public function getParent()
    {
        return CollectionType::class;
    }
}
