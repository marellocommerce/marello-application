<?php

namespace Marello\Bundle\OrderBundle\Form\EventListener;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Provider\OrderItemsSubtotalProvider;
use Marello\Bundle\PricingBundle\Subtotal\Provider\SubtotalProviderInterface;
use Marello\Bundle\TaxBundle\Provider\TaxSubtotalProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class OrderTotalsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected SubtotalProviderInterface $subtotalProvider
    ) {}

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    public function onSubmit(FormEvent $event)
    {
        /** @var Order $order */
        $order = $event->getData();

        $subtotal = $tax = 0;
        $subtotalModels = $this->subtotalProvider->getSubtotal($order);
        $total = $this->subtotalProvider->getTotal($order, $subtotalModels)->getAmount();
        foreach ($subtotalModels as $subtotalModel) {
            if ($subtotalModel->getType() === OrderItemsSubtotalProvider::TYPE) {
                $subtotal = $subtotalModel->getAmount();
                continue;
            }

            if ($subtotalModel->getType() === TaxSubtotalProvider::TYPE) {
                $tax = $subtotalModel->getAmount();
            }
        }

        $order
            ->setSubtotal($subtotal)
            ->setTotalTax($tax)
            ->setGrandTotal($total);

        $event->setData($order);
    }
}
