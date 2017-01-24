<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Form\DataTransformer\InventoryItemUpdateApiTransformer;
use Marello\Bundle\InventoryBundle\Model\InventoryItemUpdateApi;

class InventoryItemApiType extends AbstractType
{
    const NAME = 'marello_inventory_item_api';

    /** @var InventoryItemUpdateApiTransformer $transformer */
    protected $transformer;

    public function __construct(InventoryItemUpdateApiTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stock', 'number')
            ->add('warehouse', 'entity', [
                'class' => Warehouse::class,
            ]);

        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InventoryItemUpdateApi::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
