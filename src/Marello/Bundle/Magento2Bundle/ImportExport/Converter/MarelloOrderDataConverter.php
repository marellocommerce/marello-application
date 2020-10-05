<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class MarelloOrderDataConverter extends AbstractTreeDataConverter
{
    public const ORDER_STATUS_ID = 'orderStatus';
    public const ORDER_REF = 'orderReference';
    public const COUPON_CODE = 'couponCode';
    public const PAYMENT_METHOD = 'paymentMethod';
    public const PAYMENT_DETAILS = 'paymentDetails';
    public const SHIPPING_METHOD = 'shippingMethod';
    public const SHIPPING_METHOD_DETAILS = 'shippingMethodDetails';
    public const BILLING_ADDRESS = 'billingAddress';
    public const SHIPPING_ADDRESS = 'shippingAddress';
    public const PURCHASE_DATE = 'purchaseDate';
    public const SHIPPING_AMOUNT_INCL_TAX = 'shippingAmountInclTax';
    public const SHIPPING_AMOUNT_EXCL_TAX = 'shippingAmountExclTax';
    public const TOTAL_TAX = 'totalTax';
    public const DISCOUNT_AMOUNT = 'discountAmount';
    public const SUBTOTAL = 'subtotal';
    public const GRAND_TOTAL = 'grandTotal';
    public const ITEMS = 'items';

    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            self::ORDER_REF => 'orderReference',
            self::ORDER_STATUS_ID => 'orderStatus:id',
            self::COUPON_CODE => 'couponCode',
            self::PAYMENT_METHOD => 'paymentMethod',
            self::PAYMENT_DETAILS => 'paymentDetails',
            self::SHIPPING_METHOD => 'shippingMethod',
            self::SHIPPING_METHOD_DETAILS => 'shippingMethodDetails',
            self::SHIPPING_ADDRESS => 'shippingAddress',
            self::BILLING_ADDRESS => 'billingAddress' ,
            self::SHIPPING_AMOUNT_INCL_TAX => 'shippingAmountInclTax',
            self::SHIPPING_AMOUNT_EXCL_TAX => 'shippingAmountExclTax',
            self::TOTAL_TAX => 'totalTax',
            self::DISCOUNT_AMOUNT => 'discountAmount',
            self::SUBTOTAL => 'subtotal',
            self::GRAND_TOTAL => 'grandTotal',
            self::PURCHASE_DATE => 'purchaseDate',
            self::ITEMS => 'items',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if ($this->context && $this->context->hasOption('salesChannel')) {
            $importedRecord['salesChannel:id'] = $this->context->getOption('salesChannel');
        }

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * @return array|void
     */
    protected function getBackendHeader()
    {
        throw new RuntimeException('Normalization is not implemented!');
    }
}
