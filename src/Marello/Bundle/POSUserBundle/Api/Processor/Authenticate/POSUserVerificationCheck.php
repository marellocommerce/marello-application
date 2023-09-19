<?php

namespace Marello\Bundle\POSUserBundle\Api\Processor\Authenticate;

use Marello\Bundle\POSUserBundle\Api\Model\POSUserApi;
use Marello\Bundle\POSUserBundle\Api\Processor\NormalizeCredentialsData;
use Marello\Bundle\POSUserBundle\Provider\POSUserAuthProvider;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Request\Constraint;
use Oro\Bundle\ApiBundle\Model\Error;

class POSUserVerificationCheck implements ProcessorInterface
{
    public function __construct(
        private POSUserAuthProvider $authenticationProvider
    ) {}

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var AuthenticationContext $context */
        if ($context->getClassName() !== POSUserApi::class) {
            return;
        }

        $requestData = $context->getRequestData();
        if (!isset($requestData[NormalizeCredentialsData::USERNAME_KEY])) {
            $context->addError(
                Error::createValidationError(
                    Constraint::REQUEST_DATA,
                    'Either the username or email has not been sent with your request'
                )
            );
            return;
        }

        $username = $requestData[NormalizeCredentialsData::USERNAME_KEY];
        $credentials = $requestData[NormalizeCredentialsData::CREDENTIALS_KEY];
        if (!$this->authenticationProvider->authenticatePOSUser($username, $credentials)) {
            $context->addError(
                Error::createValidationError(
                    Constraint::REQUEST_DATA,
                    'Could not validate user with specified username and credentials'
                )
            );
            return;
        }

        // remove credentials from context
        unset($requestData[NormalizeCredentialsData::CREDENTIALS_KEY]);
        // set updated request data
        $context->setRequestData($requestData);
    }
}
