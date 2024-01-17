<?php

namespace Marello\Bundle\WebhookBundle\Twig;

use Marello\Bundle\WebhookBundle\Event\Provider\WebhookEventProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WebhookExtension extends AbstractExtension
{
    const NAME = 'marello_webhook';

    public function __construct(
        private WebhookEventProvider $provider
    ) {}

    public function getName()
    {
        return static::NAME;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'get_marello_webhook_event_label',
                [$this->provider, 'getLabel']
            )
        ];
    }
}
