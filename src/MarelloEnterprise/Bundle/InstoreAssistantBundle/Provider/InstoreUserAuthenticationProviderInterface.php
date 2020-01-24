<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

interface InstoreUserAuthenticationProviderInterface extends AuthenticationProviderInterface
{
    public function authenticateInstoreUser($username, $credentials);
}
