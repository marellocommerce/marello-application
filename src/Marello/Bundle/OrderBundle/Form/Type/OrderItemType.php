<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Form\DataTransformer\TaxCodeToCodeTransformer;
use Marello\Bundle\OrderBundle\Form\EventListener\OrderItemPurchasePriceSubscriber;
use Marello\Bundle\OrderBundle\Validator\Constraints\AvailableInventoryConstraint;
use Marello\Bundle\PricingBundle\DependencyInjection\Configuration;
use Marello\Bundle\ProductBundle\Form\Type\ProductSalesChannelAwareSelectType;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class OrderItemType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_order_item';

    /**
     * @var TaxCodeToCodeTransformer
     */
    protected $taxCodeModelTransformer;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param TaxCodeToCodeTransformer $taxCodeModelTransformer
     * @param ConfigManager $configManager
     */
    public function __construct(TaxCodeToCodeTransformer $taxCodeModelTransformer, ConfigManager $configManager)
    {
        $this->taxCodeModelTransformer = $taxCodeModelTransformer;
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', ProductSalesChannelAwareSelectType::class, [
                'required'       => true,
                'label'          => 'marello.product.entity_label',
                'create_enabled' => false
            ])
            ->add('quantity', NumberType::class, [
                'data' => 1,
                'constraints' => [new GreaterThanOrEqual(1)]
            ])
            ->add('availableInventory', NumberType::class, [
                'mapped' => false,
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('productUnit', TextType::class, [
                'mapped' => false,
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('price', TextType::class, [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('tax', TextType::class, [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('taxCode', TextType::class, [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('rowTotalExclTax', TextType::class, [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('rowTotalInclTax', TextType::class, [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('allocationExclusion', CheckboxType::class, [])
        ;

        $builder->get('taxCode')->addModelTransformer($this->taxCodeModelTransformer);
        $builder->addEventSubscriber(
            new OrderItemPurchasePriceSubscriber($this->configManager->get(Configuration::VAT_SYSTEM_CONFIG_PATH))
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                // Hack to set the same owner for OrderItem as for Order
                // We need to fill not only data value, but also set this data for a form to avoid validation error
                $parentOwnerField = $event->getForm()->getParent()->getParent()->get('owner');
                $data = $parentOwnerField->getData();
                $viewData = $parentOwnerField->getViewData();
                $event->getForm()->get('owner')->setData($data);
                $event->setData(['owner' => $viewData] + $event->getData());
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class,
            'constraints' => [
                new AvailableInventoryConstraint(['fields' => ['quantity', 'order', 'product']])
            ]
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
