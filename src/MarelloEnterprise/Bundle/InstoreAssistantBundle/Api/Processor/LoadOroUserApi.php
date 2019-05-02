<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor;

use Oro\Bundle\UserBundle\Entity\UserApi as EntityUserApi;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\NormalizeCredentialsData as RequestDoc;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Manager\OroUserManagerInterface;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate\AuthenticationContext;

class LoadOroUserApi implements ProcessorInterface
{
    const USER_API_ENTITY_LOADED = 'user_api_entity_loaded';

    /** @var OroUserManagerInterface $userManager */
    private $userManager;

    /**
     * @param OroUserManagerInterface $userManager
     */
    public function __construct(OroUserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     */
    public function process(ContextInterface $context)
    {
        /** @var AuthenticationContext $context */
        if ($context->hasResult() && $context->get(self::USER_API_ENTITY_LOADED)) {
            // the entity is already fetched and loaded into the context
            return;
        }

        $requestData = $context->getRequestData();
        $username = $requestData[RequestDoc::USERNAME_KEY];
        $user = $this->getUserByUsername($username);
        /** @var EntityUserApi $userApi */
        $userApi = $this->userManager->getApi($user, $user->getOrganization());
        if (!$context->hasErrors() && $userApi) {
            $context->setResult($userApi);
            $context->setId((string) $userApi->getId());
            $context->set(self::USER_API_ENTITY_LOADED, true);
        }
    }

    /**
     * Get User by it's username
     * @param string $username
     * @return mixed
     */
    protected function getUserByUsername($username)
    {
        return $this->userManager->findUserByUsernameOrEmail($username);
    }
}
