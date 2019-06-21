<?php

namespace Marello\Bundle\SubscriptionBundle\Form\EventListener;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class SubscriptionProductSubscriber implements EventSubscriberInterface
{
    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'postSetData',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        /** @var Product $product */
        $product = $event->getData();
        $form = $event->getForm();

        if (!$product instanceof ProductInterface) {
            return;
        }

        if ($form->has('subscriptionDuration') && $form->has('paymentTerm')) {
            $this->processRequiredFields($form, 'subscriptionDuration');
            $this->processRequiredFields($form, 'paymentTerm');
        }
    }

    /**
     * @param FormInterface $form
     * @param string $fieldName
     */
    private function processRequiredFields(FormInterface $form, $fieldName)
    {
        /** @var FormInterface $subscriptionDurationForm */
        $child = $form->get($fieldName);
        $config = $child->getConfig();
        $childName = $child->getName();
        $type = get_class($config->getType()->getInnerType());
        $options = $config->getOptions();

        $options['required'] = true;
        $options['constraints'] = [new NotNull()];

        $form->add($childName, $type, $options);
    }
}
