<?php

namespace Marello\Bundle\WebhookBundle\Model;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Updates security token with Integration Organization and `owner_description` attribute.
 */
trait IntegrationTokenAwareTrait
{
    protected TokenStorageInterface $tokenStorage;

    /**
     * @param Integration $integration
     * @return void
     */
    protected function setTemporaryIntegrationToken(Integration $integration): void
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return;
        }

        if ($token instanceof OrganizationAwareTokenInterface) {
            $token->setOrganization($integration->getOrganization());
        }
        $token->setAttribute('owner_description', 'Integration: '. $integration->getName());

        $this->tokenStorage->setToken($token);
    }
}
