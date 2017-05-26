<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
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
                    'read_only'      => true,
                    'required'       => true,
                    'label'          => 'marello.supplier.entity_label',
                    'create_enabled' => false,
                ]
            )
//            ->add(
//                'itemsAdvice',
//                PurchaseOrderItemAdviceCollectionType::NAME,
//                [
//                    'mapped'             => false,
//                    'cascade_validation' => true,
//                ]
//            )
            ->add(
                'itemsAdvice',
                'oro_multiple_entity',
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
        ;

        /**
         * Add purchase order items that are not mapped in the form
         */
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $form = $event->getForm();
            $purchaseOrder = $event->getData();

            /** @var PurchaseOrderItem $item */
            foreach ($form->get('itemsAdvice')->getData() as $item) {
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

        $view->children['itemsAdvice']->vars['grid_url'] = $this->router->generate('marello_purchase_order_widget_products_by_supplier', array(
            'id' => $purchaseOrder->getId(),
            'supplierId' => $purchaseOrder->getSupplier()->getId()
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrder::class,
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
