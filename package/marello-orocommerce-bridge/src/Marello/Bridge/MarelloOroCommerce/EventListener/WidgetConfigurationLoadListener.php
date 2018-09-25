<?php

namespace Marello\Bridge\MarelloOroCommerce\EventListener;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DashboardBundle\Event\WidgetConfigurationLoadEvent;

class WidgetConfigurationLoadListener
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    /**
     * @param WidgetConfigurationLoadEvent $event
     */
    public function onConfigurationLoad(WidgetConfigurationLoadEvent $event)
    {
        $configuration = $event->getConfiguration();
        if (isset($configuration['label']) && strpos($configuration['label'], 'marello') === 0) {
            $configuration['label'] = sprintf('%s %s', 'Marello', $this->translator->trans($configuration['label']));
            $event->setConfiguration($configuration);
        }
        if (isset($configuration['items']) && is_array($configuration['items'])) {
            foreach ($configuration['items'] as &$item) {
                $translatedLabel = $this->translator->trans($item['label']);
                if (isset($item['label']) && strpos($item['label'], 'marello') === 0 &&
                    !strpos($translatedLabel, 'Marello')) {
                    $item['label'] = sprintf('%s %s', 'Marello', $this->translator->trans($item['label']));
                }
                unset($item);
            }
            $event->setConfiguration($configuration);
        }
    }
}
