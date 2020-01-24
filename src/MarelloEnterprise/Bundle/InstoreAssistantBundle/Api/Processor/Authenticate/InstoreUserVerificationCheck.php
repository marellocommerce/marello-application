<?php


namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Request\Constraint;
use Oro\Bundle\ApiBundle\Model\Error;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\NormalizeCredentialsData as RequestDoc;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Provider\InstoreUserAuthenticationProviderInterface;

class InstoreUserVerificationCheck implements ProcessorInterface
{
    /** @var InstoreUserAuthenticationProviderInterface $authenticationProvider */
    private $authenticationProvider;

    /**
     * {@inheritdoc}
     * @param InstoreUserAuthenticationProviderInterface $authenticationProvider
     */
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
        if (!isset($requestData[RequestDoc::USERNAME_KEY])) {
            $context->addError(
                Error::createValidationError(
                    Constraint::REQUEST_DATA,
                    'Either the username or email has not been sent with your request'
                )
            );
            return;
        }

        $username = $requestData[RequestDoc::USERNAME_KEY];
        $credentials = $requestData[RequestDoc::CREDENTIALS_KEY];
        if (!$this->authenticationProvider->authenticateInstoreUser($username, $credentials)) {
            $context->addError(
                Error::createValidationError(
                    Constraint::REQUEST_DATA,
                    'Could not validate user with specified username and credentials'
                )
            );
            return;
        }

        // remove credentials from context
        unset($requestData[RequestDoc::CREDENTIALS_KEY]);
        // set updated request data
        $context->setRequestData($requestData);
    }
}
