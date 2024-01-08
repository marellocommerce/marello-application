<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Helper;

use Marello\Bundle\ManualShippingBundle\Tests\Functional\DataFixtures\LoadManualShippingIntegration;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

trait ManualShippingIntegrationTrait
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
    protected function getManualShippingIdentifier()
    {
        $channel = $this->getChannelReference();

        return sprintf('manual_shipping_%s', $channel->getId());
    }

    /**
     * @return string
     */
    protected function getManualShippingPrimaryIdentifier()
    {
        return 'primary';
    }

    /**
     * @return Channel
     */
    protected function getChannelReference()
    {
        return $this->getReference(LoadManualShippingIntegration::REFERENCE_MANUAL_SHIPPING);
    }
}
