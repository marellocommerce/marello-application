<?php

namespace Marello\Bundle\PaymentTermBundle\Integration;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Marello\Bundle\PaymentTermBundle\Entity\MarelloPaymentTermSettings;
use Marello\Bundle\PaymentTermBundle\Form\Type\PaymentTermSettingsType;

class PaymentTermTransport implements TransportInterface
{
    /**
     * @param Transport $transportEntity
     */
    public function init(Transport $transportEntity)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return PaymentTermSettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return MarelloPaymentTermSettings::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.paymentterm.settings.label';
    }
}
