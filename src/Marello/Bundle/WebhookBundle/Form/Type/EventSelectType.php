<?php

namespace Marello\Bundle\WebhookBundle\Form\Type;

use Marello\Bundle\WebhookBundle\Provider\WebhookEventProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventSelectType extends AbstractType
{
    public const BLOCK_PREFIX = 'marello_webhook_event_select';


    /**
     * @var WebhookEventProvider
     */
    protected WebhookEventProvider $provider;

    /**
     * @param WebhookEventProvider $provider
     */
    public function __construct(WebhookEventProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices'  => $this->getChoices(),
                'multiple' => false,
                'configs'  => [
                    'width'      => '400px',
                    'allowClear' => true,
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return Select2ChoiceType::class;
    }

    /**
     * @return array
     */
    private function getChoices()
    {
        $choices = [];
        foreach ($this->provider->getEvents() as $event) {
            $choices[$event->getLabel()] = $event->getName();
        }

        return $choices;
    }
}
