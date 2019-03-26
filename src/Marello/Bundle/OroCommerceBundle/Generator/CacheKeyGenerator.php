<?php

namespace Marello\Bundle\OroCommerceBundle\Generator;

use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Symfony\Component\HttpFoundation\ParameterBag;

class CacheKeyGenerator implements CacheKeyGeneratorInterface
{
    const PRODUCT_UNIT = 'product_unit';
    const CUSTOMER_TAX_CODE = 'customer_tax_code';
    const PRICE_LIST = 'price_list';
    const PRODUCT_FAMILY = 'product_family';
    const WAREHOUSE = 'warehouse';

    /**
     * {@inheritdoc}
     */
    public function generateKey(ParameterBag $parameters)
    {
        $parameters = $parameters->all();

        return (string)crc32(
            implode(
                '',
                [
                    $parameters[OroCommerceSettings::URL_FIELD],
                    $parameters[OroCommerceSettings::USERNAME_FIELD],
                    $parameters[OroCommerceSettings::KEY_FIELD],
                ]
            )
        );
    }
}
