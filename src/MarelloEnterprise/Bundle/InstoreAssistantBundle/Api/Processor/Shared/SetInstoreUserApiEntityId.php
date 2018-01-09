<?php


namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Shared;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Processor\SingleItemContext;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
 * Adds an identifier of the current logged in user to the Context.
 */
class InstoreUserVerificationCheck implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var SingleItemContext $context */

       if (!$context->getClassName() instanceof InstoreUserApi) {
           return;
       }
    }
}
