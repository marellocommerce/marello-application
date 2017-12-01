<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PurchaseOrderBundle\Validator\Constraints\PurchaseOrderConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\FormBundle\Form\Type\MultipleEntityType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;

class PurchaseOrderCreateStepTwoType extends AbstractType
{
    const NAME = 'marello_purchase_order_create_step_two';

    /**
     * @var Router
     */
    protected $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
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

        /**
         * Add purchase order items that are not mapped in the form
         */
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $purchaseOrder = $event->getData();

            /** @var PurchaseOrderItem $item */
            foreach ($form->get('items')->getData() as $item) {
                $purchaseOrder->addItem($item);
            }

            /** @var PurchaseOrderItem $item */
            foreach ($form->get('itemsAdditional')->getData() as $item) {
                $purchaseOrder->addItem($item);
            }
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
