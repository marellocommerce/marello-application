<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;

class PurchaseOrderItemSubscriber implements EventSubscriberInterface
{
    const LAST_PARTIALLY_RECEIVED_QTY = 'last_partially_received_qty';

    /** @var string */
    public $message = 'Received amount cannot be greater than Ordered amount.';

    /** @var TranslatorInterface $translator */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT       => 'postSubmitValidation',
        ];
    }

    /**
     * {@inheritdoc}
     * @param FormEvent $event
     */
    public function postSubmitValidation(FormEvent $event)
    {
        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = $event->getData();
        if ($acceptedQtyAttribute = $event->getForm()->get('accepted_qty')) {
            /** @var int */
            $lastReceivedAmount = $acceptedQtyAttribute->getData();
            if ($lastReceivedAmount < 0) {
                return;
            }

            $newReceiveAmount = $purchaseOrderItem->getReceivedAmount() + $lastReceivedAmount;

            if ($newReceiveAmount > $purchaseOrderItem->getOrderedAmount()) {
                $acceptedQtyAttribute->addError($this->getError());
                return;
            }

            $data = $purchaseOrderItem->getData();
            if (!$data) {
                $data = [];
            }

            if ($lastReceivedAmount) {
                $data[self::LAST_PARTIALLY_RECEIVED_QTY] = $lastReceivedAmount;
                $purchaseOrderItem->setData($data);
            }

            $purchaseOrderItem->setReceivedAmount($newReceiveAmount);
            $event->setData($purchaseOrderItem);
        }
    }

    /**
     * {@inheritdoc}
     * @return FormError
     */
    private function getError()
    {
        $message = $this->translator->trans($this->message);
        return new FormError($message);
    }
}
