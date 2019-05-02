<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ReplenishmentOrderItemType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_replenishment_order_item';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'productSku',
                TextType::class,
                [
                    'required'       => true,
                    'label'          => 'marelloenterprise.replenishment.replenishmentorderitem.product_sku.label',
                    'disabled'       => true,
                ]
            )
            ->add(
                'productName',
                TextType::class,
                [
                    'required'       => true,
                    'label'          => 'marelloenterprise.replenishment.replenishmentorderitem.product_name.label',
                    'disabled'       => true,
                ]
            )
            ->add(
                'inventoryQty',
                NumberType::class,
                [
                    'required'       => true,
                    'label'          => 'marelloenterprise.replenishment.replenishmentorderitem.inventory_qty.label',
                    'constraints'    => [new NotBlank(), new Type(['type' => 'numeric'])]
                ]
            )
            ->add(
                'note',
                TextType::class,
                [
                    'required'       => true,
                    'label'          => 'marelloenterprise.replenishment.replenishmentorderitem.note.label',
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReplenishmentOrderItem::class,
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
