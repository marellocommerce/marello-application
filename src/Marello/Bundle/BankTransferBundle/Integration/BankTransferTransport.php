<?php

namespace Marello\Bundle\BankTransferBundle\Integration;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Marello\Bundle\BankTransferBundle\Entity\BankTransferSettings;
use Marello\Bundle\BankTransferBundle\Form\Type\BankTransferSettingsType;

class BankTransferTransport implements TransportInterface
{
    /**
     * {@inheritDoc}
     */
    public function init(Transport $transportEntity)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsFormType()
    {
        return BankTransferSettingsType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsEntityFQCN()
    {
        return BankTransferSettings::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'oro.bank_transfer.settings.label';
    }
}
