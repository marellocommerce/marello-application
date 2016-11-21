<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PurchaseOrderItemSubscriber implements EventSubscriberInterface
{
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
        /** @var Collection|PurchaseOrderItem $data */
        $data = $event->getData();
        if ($acceptedQtyAttribute = $event->getForm()->get('accepted_qty')) {
            /** @var int */
            $receiveAmount = $acceptedQtyAttribute->getData();
            if ($receiveAmount < 0) {
                return;
            }

            $newReceiveAmount = $data->getReceivedAmount() + $receiveAmount;

            if ($newReceiveAmount > $data->getOrderedAmount()) {
                $acceptedQtyAttribute->addError($this->getError());
                return;
            }

            $data->setReceivedAmount($data->getReceivedAmount() + $receiveAmount);
            $event->setData($data);
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
