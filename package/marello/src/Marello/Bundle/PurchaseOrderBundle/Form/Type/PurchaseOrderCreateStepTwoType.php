<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\PurchaseOrderBundle\Validator\Constraints\PurchaseOrderConstraint;
use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;
use Oro\Bundle\FormBundle\Form\Type\MultipleEntityType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Router;

class PurchaseOrderCreateStepTwoType extends AbstractType
{
    const NAME = 'marello_purchase_order_create_step_two';

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var CurrencyNameHelper
     */
    protected $currencyNameHelper;

    /**
     * @param Router $router
     * @param CurrencyNameHelper $currencyNameHelper
     */
    public function __construct(Router $router, CurrencyNameHelper $currencyNameHelper)
    {
        $this->router = $router;
        $this->currencyNameHelper = $currencyNameHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'supplier',
                'marello_supplier_select_form',
                [
                    'attr'           => ['readonly' => true],
                    'required'       => true,
                    'label'          => 'marello.supplier.entity_label',
                    'create_enabled' => false,
                ]
            )
            ->add(
                'dueDate',
                OroDateType::NAME,
                [
                    'required' => false,
                    'label' => 'marello.purchaseorder.due_date.label',
                ]
            )
            ->add(
                'itemsAdvice',
                MultipleEntityType::class,
                [
                    'mapped'                => false,
                    'add_acl_resource'      => 'marello_purchase_order_view',
                    'class'                 => 'MarelloPurchaseOrderBundle:PurchaseOrderItem',
                    'default_element'       => 'default_purchase_order_item',
                    'required'              => false,
                    'selector_window_title' => 'marello.product.entity_label',
                ]
            )
            ->add(
                'itemsAdditional',
                PurchaseOrderItemCollectionType::NAME,
                [
                    'mapped'             => false,
                    'cascade_validation' => true,
                ]
            )
            ->add(
                'items',
                PurchaseOrderItemCollectionType::NAME,
                [
                    'mapped'             => false,
                    'cascade_validation' => true,
                ]
            )
        ;
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var PurchaseOrder $purchaseOrder */
            $purchaseOrder = $event->getData();
            $form = $event->getForm();
            if ($purchaseOrder && $supplier = $purchaseOrder->getSupplier()) {
                if ($currency = $supplier->getCurrency()) {
                    $currencySymbol = $this->currencyNameHelper->getCurrencyName($currency);
                    $form->remove('itemsAdditional');
                    $form->add(
                        'itemsAdditional',
                        PurchaseOrderItemCollectionType::NAME,
                        [
                            'mapped' => false,
                            'cascade_validation' => true,
                            'entry_options' => [
                                'currency' => $currency,
                                'currency_symbol' => $currencySymbol
                            ]
                        ]
                    );
                    $form->remove('items');
                    $form->add(
                        'items',
                        PurchaseOrderItemCollectionType::NAME,
                        [
                            'mapped' => false,
                            'cascade_validation' => true,
                            'entry_options' => [
                                'currency' => $currency,
                                'currency_symbol' => $currencySymbol
                            ]
                        ]
                    );
                }
            }
        });
        /**
         * Add purchase order items that are not mapped in the form
         */
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var PurchaseOrder $purchaseOrder */
            $purchaseOrder = $event->getData();
            $orderTotal = 0.00;
            /** @var PurchaseOrderItem $item */
            foreach ($form->get('items')->getData() as $item) {
                if ($item->getProduct()) {
                    $price = $item->getPurchasePrice();
                    $price->setProduct($item->getProduct())->setCurrency($purchaseOrder->getSupplier()->getCurrency());
                    $item->setRowTotal($item->getPurchasePrice()->getValue() * $item->getOrderedAmount());
                    $orderTotal += $item->getRowTotal();
                    $purchaseOrder->addItem($item);
                }
            }

            /** @var PurchaseOrderItem $item */
            foreach ($form->get('itemsAdditional')->getData() as $item) {
                if ($item->getProduct()) {
                    $price = $item->getPurchasePrice();
                    $price->setProduct($item->getProduct())->setCurrency($purchaseOrder->getSupplier()->getCurrency());
                    $item->setRowTotal($item->getPurchasePrice()->getValue() * $item->getOrderedAmount());
                    $orderTotal += $item->getRowTotal();
                    $purchaseOrder->addItem($item);
                }
            }
            $purchaseOrder->setOrderTotal($orderTotal);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $form->getData();

        $view->children['itemsAdvice']->vars['grid_url'] = $this->router->generate(
            'marello_purchase_order_widget_products_by_supplier',
            [
                'id' => $purchaseOrder->getId(),
                'supplierId' => $form->get('supplier')->getData()->getId()
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrder::class,
            'allow_extra_fields' => true,
            'constraints' => [
                new PurchaseOrderConstraint()
            ],
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }
}
