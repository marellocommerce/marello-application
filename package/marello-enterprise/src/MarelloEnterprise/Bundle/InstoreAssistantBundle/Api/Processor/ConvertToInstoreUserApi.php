<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\ApiBundle\Request\Constraint;
use Oro\Bundle\ApiBundle\Model\Error;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate\AuthenticationContext;

class ConvertToInstoreUserApi implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     */
    public function process(ContextInterface $context)
    {
        /** @var AuthenticationContext $context */
        if (!$context->hasResult() || !$context->get(LoadOroUserApi::USER_API_ENTITY_LOADED)) {
            // the entity is not loaded do not continue
            return;
        }

        $entityClass = $context->getClassName();
        /** @var InstoreUserApi $instoreUserApi */
        $instoreUserApi = $this->convertToInstoreUserApi($context, $entityClass);
        if (!$context->hasErrors()) {
            $context->setResult($instoreUserApi);
        }
    }

    /**
     * Create a reflection class instance for the InstoreUserApi
     * @param $entityClass
     * @param UserApi $userApi
     * @return object
     */
    protected function createInstanceOfInstoreUserApi($entityClass, UserApi $userApi)
    {
        $reflectionClass = new \ReflectionClass($entityClass);
        return $reflectionClass->newInstanceArgs([$userApi->getId(), $userApi->getApiKey()]);
    }

    /**
     * Convert to InstoreUserApi from context result
     * @param AuthenticationContext $context
     * @param $entityClass
     * @return InstoreUserApi|null
     */
    protected function convertToInstoreUserApi(AuthenticationContext $context, $entityClass)
    {
        if ($entityClass !== InstoreUserApi::class) {
            $context->addError(
                Error::createValidationError(
                    Constraint::ENTITY_TYPE,
                    sprintf(
                        'EntityClass given is not of type %s. %s given',
                        InstoreUserApi::class,
                        $entityClass
                    )
                )
            );
            return null;
        }

        /** @var UserApi $userApi */
        $userApi = $context->getResult();

        if (!$userApi instanceof UserApi) {
            $context->addError(
                Error::createValidationError(
                    Constraint::ENTITY_TYPE,
                    sprintf(
                        'EntityClass given is not of type %s. %s given',
                        UserApi::class,
                        get_class($userApi)
                    )
                )
            );
            return null;
        }
        /** @var InstoreUserApi $instoreUserApi */
        $instoreUserApi = $this->createInstanceOfInstoreUserApi($entityClass, $userApi);

        return $instoreUserApi;
    }
}
