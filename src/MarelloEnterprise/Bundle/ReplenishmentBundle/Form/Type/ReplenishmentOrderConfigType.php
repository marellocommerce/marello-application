<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type;

use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseMultiSelectType;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

class ReplenishmentOrderConfigType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_replenishment_order_config';
    /**
     * @var Router
     */
    protected $router;
    
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param Router $router
     * @param ObjectManager $manager
     */
    public function __construct(Router $router, ObjectManager $manager)
    {
        $this->router = $router;
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
                    'required' => true,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderconfig.origins.label',
                    'placeholder' => 'marelloenterprise.inventory.warehouse.form.select_warehouse',
                    'mapped'=> false,
                    'constraints' => new NotNull()
                ]
            )
            ->add(
                'destinations',
                WarehouseMultiSelectType::class,
                [
                    'required' => true,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderconfig.destinations.label',
                    'placeholder' => 'marelloenterprise.inventory.warehouse.form.select_warehouse',
                    'mapped' => false,
                    'constraints' => new NotNull()
                ]
            )
            ->add(
                'products',
                ReplenishmentOrderConfigProductsType::class,
                [
                    'mapped' => true,
                    'required' => true,
                ]
            )
            ->add(
                'strategy',
                ReplenishmentStrategyChoiceType::class,
                [
                    'required' => true,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderconfig.strategy.label',
                ]
            )
            ->add(
                'executionDateTime',
                OroDateTimeType::class,
                [
                    'required' => false,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderconfig.execution_date_time.label'
                ]
            )
            ->add(
                'percentage',
                NumberType::class,
                [
                    'required' => true,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderconfig.percentage.label',
                    'tooltip'
                        => 'marelloenterprise.replenishment.form.replenishmentorderconfig.percentage.tooltip',
                    'constraints' => new Range(['min' => 1, 'max' => 100]),
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
        ;
        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();
                $data['products'] = $data['products']['added'];
                $event->setData($data);
                if ($form->has('products')) {
                    $form->remove('products');
                    $form->add(
                        'products',
                        TextType::class,
                        ['required' => false]
                    );
                }
            })
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
                    $products = $form->get('products')->getViewData();
                    $data->setProducts(explode(',', $products));

                    $event->setData($data);
                }
            )
        ;
    }


    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->children['products']->vars['grid_url'] = $this->router->generate(
            'marello_replenishment_order_config_widget_products_candidates'
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
