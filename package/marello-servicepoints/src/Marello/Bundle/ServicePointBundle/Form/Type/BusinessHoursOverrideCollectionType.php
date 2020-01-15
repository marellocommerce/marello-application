<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessHoursOverrideCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'           => BusinessHoursOverrideType::class,
            'show_form_when_empty' => true,
            'allow_add'            => true,
            'prototype_name'       => '__namebusinessoverridecollection__',
            'prototype'            => true,
            'handle_primary'       => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        $x = parent::getBlockPrefix();
        var_dump($x);
        return $x;
    }

    public function getParent()
    {
        return CollectionType::class;
    }
}
