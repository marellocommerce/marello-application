<?php

namespace Marello\Bundle\Magento2Bundle\Generator;

use Marello\Bundle\Magento2Bundle\DTO\CustomerIdentityDataDTO;

/**
 * Generate skeleton of method that work on generating hash id by customer identities fields
 */
interface CustomerHashIdGeneratorInterface
{
    /**
     * @param CustomerIdentityDataDTO $customerIdentityDataDTO
     * @return string
     */
    public function generateHashId(CustomerIdentityDataDTO $customerIdentityDataDTO): string;
}
