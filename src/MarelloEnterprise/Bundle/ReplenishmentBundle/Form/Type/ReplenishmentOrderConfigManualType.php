<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReplenishmentOrderConfigManualType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_replenishment_order_config_manual';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'executionDateTime',
                OroDateTimeType::class,
                [
                    'required' => false,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderconfig.execution_date_time.label'
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => false,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderconfig.description.label'
                ]
            )
            ->add(
                'manualItems',
                ReplenishmentOrderManualItemCollectionType::class,
                [
                    'required' => true,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReplenishmentOrderConfig::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
