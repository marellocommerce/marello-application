<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

use Oro\Bundle\ApiBundle\Request\JsonApi\JsonApiDocumentBuilder as JsonApiDoc;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate\AuthenticationContext;

class NormalizeCredentialsData implements ProcessorInterface
{
    const USERNAME_KEY = 'username';
    const EMAIL_KEY = 'email';
    const CREDENTIALS_KEY = 'credentials';

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var AuthenticationContext $context */
        $requestData = $context->getRequestData();
        if (array_key_exists(JsonApiDoc::DATA, $requestData)) {
            // the request data is not yet normalized by Oro API Processors
            return;
        }

        $context->setRequestData($this->normalizeData($requestData));
    }

    /**
     * @param array  $data
     *
     * @return array
     */
    protected function normalizeData(array $data)
    {
        $username = array_key_exists(self::EMAIL_KEY, $data)
            ? $data[self::EMAIL_KEY] : $data[self::USERNAME_KEY];

        return [
            self::USERNAME_KEY => $username,
            self::CREDENTIALS_KEY => $data[self::CREDENTIALS_KEY]
        ];
    }
}
