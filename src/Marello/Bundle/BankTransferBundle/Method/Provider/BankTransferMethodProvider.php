<?php

namespace Marello\Bundle\BankTransferBundle\Method\Provider;

use Marello\Bundle\PaymentBundle\Method\Factory\IntegrationPaymentMethodFactoryInterface;
use Marello\Bundle\PaymentBundle\Method\Provider\Integration\ChannelPaymentMethodProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class BankTransferMethodProvider extends ChannelPaymentMethodProvider
{
    /**
     * {@inheritDoc}
     */
    public function __construct(
        $channelType,
        DoctrineHelper $doctrineHelper,
        IntegrationPaymentMethodFactoryInterface $methodFactory
    ) {
        parent::__construct($channelType, $doctrineHelper, $methodFactory);
    }
}
