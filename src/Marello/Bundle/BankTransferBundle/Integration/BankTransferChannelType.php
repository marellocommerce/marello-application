<?php

namespace Marello\Bundle\BankTransferBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class BankTransferChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'bank_transfer';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.bank_transfer.channel_type.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'bundles/marellobanktransfer/img/bank-transfer-icon.png';
    }
}
