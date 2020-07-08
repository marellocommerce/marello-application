<?php

namespace Marello\Bundle\Magento2Bundle\Generator;

use Marello\Bundle\CustomerBundle\Entity\Customer;

/**
 * Generate skeleton of method that work on generating hash id by customer identities fields
 */
class CustomerHashIdGenerator implements CustomerHashIdGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generateHashId(Customer $customer): string
    {
        $stringToHashing = sprintf(
            '%s_%s_%s',
            $customer->getEmail(),
            $customer->getFirstName(),
            $customer->getLastName()
        );

        return md5($stringToHashing);
    }
}
