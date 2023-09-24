<?php

namespace Marello\Bundle\POSUserBundle\Api\Processor;

use Marello\Bundle\POSUserBundle\Api\Processor\Authenticate\AuthenticationContext;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class LoadOroUserApi implements ProcessorInterface
{
    const USER_API_ENTITY_LOADED = 'user_api_entity_loaded';

    public function __construct(
        private UserManager $userManager
    ) {}

    public function process(ContextInterface $context)
    {
        /** @var AuthenticationContext $context */
        if ($context->hasResult() && $context->get(self::USER_API_ENTITY_LOADED)) {
            // the entity is already fetched and loaded into the context
            return;
        }

        $requestData = $context->getRequestData();
        $username = $requestData[NormalizeCredentialsData::USERNAME_KEY];
        $user = $this->userManager->findUserByUsernameOrEmail($username);
        /** @var UserApi $userApi */
        $userApi = $this->userManager->getApi($user, $user->getOrganization());
        if (!$context->hasErrors() && $userApi) {
            $context->setResult($userApi);
            $context->setId((string) $userApi->getId());
            $context->set(self::USER_API_ENTITY_LOADED, true);
        }
    }
}
