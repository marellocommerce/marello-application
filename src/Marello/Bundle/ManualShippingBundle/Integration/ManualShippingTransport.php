<?php

namespace Marello\Bundle\ManualShippingBundle\Integration;

use Marello\Bundle\ManualShippingBundle\Entity\ManualShippingSettings;
use Marello\Bundle\ManualShippingBundle\Form\Type\ManualShippingSettingsType;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class ManualShippingTransport implements TransportInterface
{
    /**
     * @var ParameterBag
     */
    protected $settings;

    /**
     * @param Transport $transportEntity
     */
    public function init(Transport $transportEntity)
    {
        $this->settings = $transportEntity->getSettingsBag();
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return ManualShippingSettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return ManualShippingSettings::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.manual_shipping.settings.label';
    }
}
