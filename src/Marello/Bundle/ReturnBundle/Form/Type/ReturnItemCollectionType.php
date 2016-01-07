<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReturnItemCollectionType extends AbstractType
{
    const NAME = 'marello_return_item_collection';

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type' => ReturnItemType::NAME,
        ]);
    }

    public function getParent()
    {
        return 'collection';
    }
}
