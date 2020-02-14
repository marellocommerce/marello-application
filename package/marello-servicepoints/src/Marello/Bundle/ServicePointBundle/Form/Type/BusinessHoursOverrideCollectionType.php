<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessHoursOverrideCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_servicepoint_business_hours_override_collection';

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'           => BusinessHoursOverrideType::class,
            'show_form_when_empty' => false,
            'allow_add'            => true,
            'prototype_name'       => '__namebusinessoverridecollection__',
            'prototype'            => true,
            'handle_primary'       => true,
        ]);
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
