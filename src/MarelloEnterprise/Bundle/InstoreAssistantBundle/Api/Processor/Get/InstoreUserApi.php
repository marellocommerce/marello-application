<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Get;

use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Request\Constraint;
use Oro\Bundle\ApiBundle\Util\EntityInstantiator;
use Oro\Bundle\ApiBundle\Util\EntityLoader;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Manager\OroUserManagerInterface;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate\AuthenticationContext;

class InstoreUserApi implements ProcessorInterface
{
    /** @var OroUserManagerInterface $userManager */
    private $userManager;

    /** @var EntityLoader */
    protected $entityLoader;

    /** @var EntityInstantiator */
    protected $entityInstantiator;

    /**
     * @param EntityLoader       $entityLoader
     * @param EntityInstantiator $entityInstantiator
     * @param OroUserManagerInterface $userManager
     */
    public function __construct(
        EntityLoader $entityLoader,
        EntityInstantiator $entityInstantiator,
        OroUserManagerInterface $userManager
    ) {
        $this->entityLoader = $entityLoader;
        $this->entityInstantiator = $entityInstantiator;
        $this->userManager = $userManager;
    }

    public function process(ContextInterface $context)
    {
        /** @var AuthenticationContext $context */
        if ($context->hasResult()) {
            // the entity already exists
            return;
        }

        $entityClass = $context->getClassName();
        $username = $context->getRequestData()['username'];
        $user = $this->getUserByUsername($username);
        /** @var UserApi $userApi */
        $userApi = $this->userManager->getApi($user, $user->getOrganization());
        if (!$context->hasErrors()) {
            $reflClass = new \ReflectionClass($entityClass);
            $instoreUserApi = $reflClass->newInstance();
            $instoreUserApi->setApiKey($userApi->getApiKey());
            $instoreUserApi->setId($userApi->getId());
            $context->setResult(['id' => $instoreUserApi->getId(), 'apiKey' => $instoreUserApi->getApiKey()]);
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
