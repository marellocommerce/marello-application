<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 16-3-18
 * Time: 10:48
 */

namespace Marello\Bundle\MageBridgeBundle\OAuth\ResourceOwner;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

trait IntegrationChannelTrait
{
    /** @var Integration $integrationChannel */
    protected $integrationChannel;

    /**
     * @return Integration
     */
    public function getIntegrationChannel()
    {
        return $this->integrationChannel;
    }

    /**
     * @param $integrationChannel
     * @return $this
     */
    public function setIntegrationChannel(Integration $integrationChannel)
    {
        $this->integrationChannel = $integrationChannel;

        return $this;
    }

    public function configureCredentials()
    {
        /** @var Integration $integrationChannel */
        $integrationChannel = $this->getIntegrationChannel();

        $this->options['client_id'] = $integrationChannel->getClientId();
        $this->options['client_secret'] = $integrationChannel->getClientSecret();
        $this->options['access_token_url'] = $integrationChannel->getClientSecret();

        return $this;
    }


}
