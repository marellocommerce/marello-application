<?php

namespace Marello\Bundle\PaymentBundle\Tests\Functional\Helper;

use Marello\Bundle\PaymentTermBundle\Tests\Functional\DataFixtures\LoadPaymentTermIntegration;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

trait PaymentTermIntegrationTrait
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    abstract public function getReference(string $name);

    /**
     * @return string
     */
    protected function getPaymentTermIdentifier()
    {
        $channel = $this->getChannelReference();

        return sprintf('payment_term_%s', $channel->getId());
    }

    /**
     * @return Channel
     */
    protected function getChannelReference()
    {
        return $this->getReference(LoadPaymentTermIntegration::REFERENCE_PAYMENT_TERM);
    }
}
