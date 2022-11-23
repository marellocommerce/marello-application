<?php

namespace Marello\Bundle\TaskBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupSelectType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'create_enabled' => false,
                'autocomplete_alias' => 'groups',
                'grid_name' => 'groups-select-grid',
            ]
        );
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'marello_group_select';
    }
}
