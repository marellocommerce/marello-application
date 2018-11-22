<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Form\Type\ProductSelectCollectionType;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseMultiSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReplenishmentOrderConfigType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_replenishment_order_config';

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'origins',
                WarehouseMultiSelectType::class,
                [
                    'required'       => true,
                    'label'          => 'marello.replenishment.replenishment_order_config.origins.label',
                    'placeholder'    => 'marelloenterprise.inventory.warehouse.form.select_warehouse',
                    'mapped'         => false,
                ]
            )
            ->add(
                'destinations',
                WarehouseMultiSelectType::class,
                [
                    'required'       => true,
                    'label'          => 'marello.replenishment.replenishment_order_config.destinations.label',
                    'placeholder'    => 'marelloenterprise.inventory.warehouse.form.select_warehouse',
                    'mapped'         => false,
                ]
            )
            ->add(
                'products',
                ProductSelectCollectionType::class,
                [
                    'required'       => true,
                    'label'          => 'marello.replenishment.replenishment_order_config.products.label',
                    'mapped'         => false,
                ]
            )
            ->add(
                'strategy',
                ReplenishmentStrategyChoiceType::class,
                [
                    'required' => true,
                    'label' => 'marello.replenishment.replenishment_order_config.strategy.label',
                ]
            )
            ->add(
                'executionDate',
                OroDateType::class,
                [
                    'required' => true,
                    'label' => 'marello.replenishment.replenishment_order_config.execution_date.label',
                ]
            )
            ->add(
                'percentage',
                NumberType::class,
                [
                    'required' => true,
                    'label' => 'marello.replenishment.replenishment_order_config.percentage.label',
                    'tooltip' => 'marello.replenishment.form.marello_replenishment_order_config.percentage.tooltip'
                ]
            )
        ;
        $builder
            ->addEventListener(
                FormEvents::POST_SET_DATA,
                function (FormEvent $event) {
                    /** @var ReplenishmentOrderConfig $data */
                    $data = $event->getData();
                    if (!$data instanceof ReplenishmentOrderConfig) {
                        return;
                    }
                    $form = $event->getForm();

                    $warehouseRepository = $this->manager->getRepository(Warehouse::class);
                    $productRepository = $this->manager->getRepository(Product::class);

                    $origins = $warehouseRepository->findBy(['id' => $data->getOrigins()]);
                    $destinations = $warehouseRepository->findBy(['id' => $data->getDestinations()]);
                    $products = $productRepository->findBy(['id' => $data->getProducts()]);

                    $form->get('origins')->setData($origins);
                    $form->get('destinations')->setData($destinations);
                    $form->get('products')->setData($products);
                }
            )
            ->addEventListener(
                FormEvents::SUBMIT,
                function (FormEvent $event) {
                    /** @var ReplenishmentOrderConfig $data */
                    $data = $event->getData();
                    if (!$data instanceof ReplenishmentOrderConfig) {
                        return;
                    }
                    $form = $event->getForm();

                    /** @var Warehouse[] $origins */
                    $origins = $form->get('origins')->getData();
                    $originsArray = [];
                    foreach ($origins as $origin) {
                        $originsArray[] = $origin->getId();
                    }
                    $data->setOrigins($originsArray);

                    /** @var Warehouse[] $destinations */
                    $destinations = $form->get('destinations')->getData();
                    $destinationsArray = [];
                    foreach ($destinations as $destination) {
                        $destinationsArray[] = $destination->getId();
                    }
                    $data->setDestinations($destinationsArray);

                    /** @var Product[] $products */
                    $products = $form->get('products')->getData();
                    $productsArray = [];
                    foreach ($products as $product) {
                        $productsArray[] = $product->getId();
                    }
                    $data->setProducts($productsArray);

                    $event->setData($data);
                }
            )
        ;
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