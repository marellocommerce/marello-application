<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\PurchaseOrderBundle\Form\EventListener\PurchaseOrderItemSubscriber;

class PurchaseOrderItemReceiveType extends AbstractType
{
    const NAME = 'marello_purchase_order_item_receive';

    /** @var PurchaseOrderItemSubscriber $purchaseOrderItemSubscriber */
    protected $purchaseOrderItemSubscriber;

    /**
     * PurchaseOrderItemReceiveType constructor.
     *
     * @param PurchaseOrderItemSubscriber $purchaseOrderItemSubscriber
     */
    public function __construct(PurchaseOrderItemSubscriber $purchaseOrderItemSubscriber)
    {
        $this->purchaseOrderItemSubscriber = $purchaseOrderItemSubscriber;
    }

    /**
     * {@inheritdoc}
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('accepted_qty', 'integer', [
            'mapped' => false,
            'required'  => true,
            'constraints'   => new GreaterThan(['value' => 0]),
        ]);

        $builder->addEventSubscriber($this->purchaseOrderItemSubscriber);
    }

    /**
     * {@inheritdoc}
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => PurchaseOrderItem::class,
            'error_bubbling'    => true,
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
