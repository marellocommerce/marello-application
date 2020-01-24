<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Manager;

use Symfony\Component\Security\Core\User\UserProviderInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

interface OroUserManagerInterface extends UserProviderInterface
{
    public function findUserByUsernameOrEmail($usernameOrEmail);

    public function getApi(User $user, Organization $organization);
}
