<?php

namespace Marello\Bundle\PaymentBundle\Form\EventSubscriber;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class MethodConfigCollectionSubscriber implements EventSubscriberInterface
{
    /**
     * @var PaymentMethodProviderInterface
     */
    protected $paymentMethodProvider;

    /**
     * @param PaymentMethodProviderInterface $paymentMethodProvider
     */
    public function __construct(PaymentMethodProviderInterface $paymentMethodProvider)
    {
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSet',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        /** @var Collection|PaymentMethodConfig[] $data */
        $data = $event->getData();
        $form = $event->getForm();

        if (!$data) {
            return;
        }

        foreach ($data as $index => $methodConfig) {
            $paymentMethod = $this->paymentMethodProvider->getPaymentMethod($methodConfig->getMethod());
            if (!$paymentMethod) {
                $data->remove($index);
                $form->remove($index);
            }
        }
        $event->setData($data);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        /** @var array $data */
        $submittedData = $event->getData();
        $form = $event->getForm();

        if (!$submittedData) {
            return;
        }

        $filteredSubmittedData = [];
        foreach ($submittedData as $index => $itemData) {
            if (array_key_exists('method', $itemData)
                && $this->paymentMethodProvider->getPaymentMethod($itemData['method']) !== null
            ) {
                $filteredSubmittedData[$index] = $itemData;
            } else {
                $form->remove($index);
            }
        }

        $event->setData($filteredSubmittedData);
    }
}
