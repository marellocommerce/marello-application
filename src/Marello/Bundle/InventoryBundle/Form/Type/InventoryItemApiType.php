<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Form\DataTransformer\InventoryItemUpdateApiTransformer;
use Marello\Bundle\InventoryBundle\Model\InventoryItemUpdateApi;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryItemApiType extends AbstractType
{
    const NAME = 'marello_inventory_item_api';

    /**
     * @var InventoryItemUpdateApiTransformer
     */
    protected $transformer;

    /**
     * @param InventoryItemUpdateApiTransformer $transformer
     */
    public function __construct(InventoryItemUpdateApiTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stock', NumberType::class)
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
            ]);

        $builder->addModelTransformer($this->transformer);
    }

    /**
     * {@inheritdoc}
     */
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
    
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
