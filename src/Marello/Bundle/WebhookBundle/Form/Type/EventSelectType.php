<?php

namespace Marello\Bundle\WebhookBundle\Form\Type;

use Marello\Bundle\WebhookBundle\Event\Provider\WebhookEventProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventSelectType extends AbstractType
{
    public const BLOCK_PREFIX = 'marello_webhook_event_select';

    public function __construct(
        protected WebhookEventProvider $provider
    ) {}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices'  => $this->provider->getEventChoices(),
                'multiple' => false,
                'configs'  => [
                    'allowClear' => true,
                ]
            ]
        );
    }

    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    public function getParent()
    {
        return Select2ChoiceType::class;
    }
}
