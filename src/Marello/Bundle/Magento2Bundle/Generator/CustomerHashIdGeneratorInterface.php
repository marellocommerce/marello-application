<?php

namespace Marello\Bundle\Magento2Bundle\Generator;

use Marello\Bundle\CustomerBundle\Entity\Customer;

/**
 * Generate skeleton of method that work on generating hash id by customer identities fields
 */
interface CustomerHashIdGeneratorInterface
{
    /**
     * @param Customer $customer
     * @return string
     */
    public function generateHashId(Customer $customer): string;
}
