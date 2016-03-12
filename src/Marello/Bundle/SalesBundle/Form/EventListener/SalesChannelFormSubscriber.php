<?php

namespace Marello\Bundle\SalesBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class SalesChannelFormSubscriber implements EventSubscriberInterface
{
    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA  => 'preSet',
            FormEvents::PRE_SUBMIT    => 'preSubmit',
        ];
    }

    /**
     * Preset data for channels
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var SalesChannel $data */
        $data = $event->getData();

        if ($data !== null) {
            $currency = $data->getCurrency();
        }

        if (!($data && $data->getId())) {
            $currency = $this->localeSettings->getCurrency();
        }

        FormUtils::replaceField($form, 'currency', ['data' => $currency]);

        $this->disableFields($form, $data);

        $event->setData($data);
    }

    /**
     * Add disable currency field pre submit
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var SalesChannel $originalData */
        $originalData = $form->getData();
        $data         = $event->getData();

        $this->disableFields($form, $originalData);

        $event->setData($data);
    }

    /**
     * Disable fields that are not allowed to be modified since channel has been saved
     *
     * @param FormInterface     $form
     * @param SalesChannel       $channel
     */
    protected function disableFields(FormInterface $form, SalesChannel $channel = null)
    {
        if (!($channel && $channel->getId())) {
            // do nothing if integration is new
            return;
        }

        if ($channel->getCurrency() !== null) {
            // disable currency field
            FormUtils::replaceField($form, 'currency', ['disabled' => true]);
        }
    }
}
