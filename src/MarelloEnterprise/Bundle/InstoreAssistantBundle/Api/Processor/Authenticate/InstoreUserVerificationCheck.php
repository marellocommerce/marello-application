<?php


namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Request\Constraint;
use Oro\Bundle\ApiBundle\Model\Error;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Provider\InstoreUserAuthenticationProviderInterface;

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
        /** @var AuthenticationContext $context */
        if ($context->getClassName() !== InstoreUserApi::class) {
           return;
        }

        $requestData = $context->getRequestData();
        if (!isset($requestData['username']) && !isset($requestData['email'])) {
            $context->addError(
                Error::createValidationError(
                    Constraint::REQUEST_DATA,
                    'The request data should not be empty'
                )
            );

        }

        $username = isset($requestData['username']) ? $requestData['username'] : $requestData['email'];
        if(!$this->authenticationProvider->authenticateInstoreUser($username, $requestData['credentials'])) {
            $context->addError(
                Error::createValidationError(
                    Constraint::REQUEST_DATA,
                    'The User you\'re trying to authenticate is not valid'
                )
            );
        }
    }
}
