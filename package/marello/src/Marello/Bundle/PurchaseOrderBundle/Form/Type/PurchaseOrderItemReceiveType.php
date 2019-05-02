<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\PurchaseOrderBundle\Form\EventListener\PurchaseOrderItemSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;

class PurchaseOrderItemReceiveType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_purchase_order_item_receive';

    /**
     * @var PurchaseOrderItemSubscriber
     */
    protected $purchaseOrderItemSubscriber;

    /**
     * @param PurchaseOrderItemSubscriber $purchaseOrderItemSubscriber
     */
    public function __construct(PurchaseOrderItemSubscriber $purchaseOrderItemSubscriber)
    {
        $this->purchaseOrderItemSubscriber = $purchaseOrderItemSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('accepted_qty', IntegerType::class, [
            'mapped' => false,
            'required'  => true,
            'constraints'   => new GreaterThan(['value' => 0]),
        ]);

        $builder->addEventSubscriber($this->purchaseOrderItemSubscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => PurchaseOrderItem::class,
            'error_bubbling'    => true,
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
