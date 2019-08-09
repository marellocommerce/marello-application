<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Manager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

interface OroUserManagerInterface
{
    public function findUserByUsernameOrEmail(string $usernameOrEmail);

    public function getApi(User $user, Organization $organization);
}
