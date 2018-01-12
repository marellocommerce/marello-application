<?php


namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Processor\SingleItemContext;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Provider\InstoreUserAuthenticationProviderInterface;
/**
 * Adds an identifier of the current logged in user to the Context.
 */
class InstoreUserVerificationCheck implements ProcessorInterface
{
    /** @var InstoreUserAuthenticationProviderInterface $authenticationProvider */
    private $authenticationProvider;

    public function __construct(InstoreUserAuthenticationProviderInterface $authenticationProvider)
    {
        $this->authenticationProvider = $authenticationProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var SingleItemContext $context */
        if ($context->getClassName() !== InstoreUserApi::class) {
           return;
        }

        if(!$this->authenticationProvider->authenticateInstoreUser($context->get('username'), $context->get('password'))) {
            throw new NotFoundHttpException('The User you\'re trying to authenticate is not valid');
        }

    }
}
