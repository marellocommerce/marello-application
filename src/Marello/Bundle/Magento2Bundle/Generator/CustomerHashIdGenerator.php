<?php

namespace Marello\Bundle\Magento2Bundle\Generator;

use Marello\Bundle\Magento2Bundle\DTO\CustomerIdentityDataDTO;

/**
 * Generate skeleton of method that work on generating hash id by customer identities fields
 */
class CustomerHashIdGenerator implements CustomerHashIdGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generateHashId(CustomerIdentityDataDTO $customerIdentityDataDTO): string
    {
        $stringToHashing = sprintf(
            '%s_%s_%s',
            $customerIdentityDataDTO->getEmail(),
            $customerIdentityDataDTO->getFirstName(),
            $customerIdentityDataDTO->getLastName()
        );

        return md5($stringToHashing);
    }
}
