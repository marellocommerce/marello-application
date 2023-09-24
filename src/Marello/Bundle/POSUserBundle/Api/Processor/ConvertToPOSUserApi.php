<?php

namespace Marello\Bundle\POSUserBundle\Api\Processor;

use Marello\Bundle\POSUserBundle\Api\Model\POSUserApi;
use Marello\Bundle\POSUserBundle\Api\Processor\Authenticate\AuthenticationContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\ApiBundle\Request\Constraint;
use Oro\Bundle\ApiBundle\Model\Error;

class ConvertToPOSUserApi implements ProcessorInterface
{
    public function process(ContextInterface $context)
    {
        /** @var AuthenticationContext $context */
        if (!$context->hasResult() || !$context->get(LoadOroUserApi::USER_API_ENTITY_LOADED)) {
            // the entity is not loaded do not continue
            return;
        }

        $entityClass = $context->getClassName();
        /** @var POSUserApi $userApi */
        $userApi = $this->convertToPOSUserApi($context, $entityClass);
        if (!$context->hasErrors()) {
            $context->setResult($userApi);
        }
    }

    protected function convertToPOSUserApi(AuthenticationContext $context, string $entityClass): ?POSUserApi
    {
        if ($entityClass !== POSUserApi::class) {
            $context->addError(
                Error::createValidationError(
                    Constraint::ENTITY_TYPE,
                    sprintf(
                        'EntityClass given is not of type %s. %s given',
                        POSUserApi::class,
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

        $reflectionClass = new \ReflectionClass($entityClass);

        return $reflectionClass->newInstanceArgs([$userApi->getId(), $userApi->getApiKey(), $userApi->getUser()->getRoles()]);
    }
}
