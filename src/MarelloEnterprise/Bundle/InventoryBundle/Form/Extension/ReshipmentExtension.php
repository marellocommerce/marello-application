<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Extension;

use Marello\Bundle\InventoryBundle\Form\Type\ReshipmentType;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\ConsolidationEnabledType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class ReshipmentExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [ReshipmentType::class];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if ($builder->has('consolidation_enabled')) {
            $builder->remove('consolidation_enabled');
        }

        $builder->add('consolidation_enabled', ConsolidationEnabledType::class, [
            'required' => false,
        ]);
    }
}
