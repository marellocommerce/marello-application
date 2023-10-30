<?php

namespace Marello\Bundle\ShippingBundle\Context;

use Oro\Bundle\LocaleBundle\Model\AddressInterface;

class ShippingContextCacheKeyGenerator
{
    /**
     * @param ShippingContextInterface $context
     * @return string
     */
    public function generateKey(ShippingContextInterface $context)
    {
        $lineItems = array_map(function (ShippingLineItemInterface $item) {
            return $this->lineItemToString($item);
        }, $context->getLineItems()->toArray());

        // if order of line item was changed, hash should not be changed
        usort($lineItems, function ($a, $b) {
            return strcmp(md5($a), md5($b));
        });

        return (string)crc32(implode('', array_merge($lineItems, [
            $context->getCurrency(),
            $context->getPaymentMethod(),
            $this->addressToString($context->getBillingAddress()),
            $this->addressToString($context->getShippingAddress()),
            $this->addressToString($context->getShippingOrigin()),
            $context->getSubtotal() ? $context->getSubtotal()->getValue() : '',
            $context->getSubtotal() ? $context->getSubtotal()->getCurrency() : '',
        ])))
        .str_replace('\\', '', $context->getSourceEntity() ? get_class($context->getSourceEntity()) : '')
        .$context->getSourceEntityIdentifier();
    }

    /**
     * @param AddressInterface|null $address
     * @return string
     */
    protected function addressToString(AddressInterface $address = null)
    {
        return $address ? implode('', [
            $address->getStreet(),
            $address->getStreet2(),
            $address->getCity(),
            $address->getRegionName(),
            $address->getRegionCode(),
            $address->getPostalCode(),
            $address->getCountryName(),
            $address->getCountryIso2(),
            $address->getCountryIso3(),
            $address->getOrganization(),
        ]) : '';
    }

    /**
     * @param ShippingLineItemInterface $item
     * @return string
     */
    protected function lineItemToString(ShippingLineItemInterface $item)
    {
        $strings = [
            $item->getEntityIdentifier(),
            $item->getQuantity()
        ];

        if ($item->getProduct()) {
            $strings[] = $item->getProduct()->getId();
            $strings[] = $item->getProduct()->getSku();
        }

        if ($item->getPrice()) {
            $strings[] = $item->getPrice()->getValue();
            $strings[] = $item->getPrice()->getCurrency();
        }

        if ($item->getWeight()) {
            $strings[] = $item->getWeight();
        }

        return implode('', $strings);
    }
}
