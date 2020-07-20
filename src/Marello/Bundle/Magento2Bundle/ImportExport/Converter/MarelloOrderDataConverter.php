<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class MarelloOrderDataConverter extends AbstractTreeDataConverter
{
    public const ORDER_NUMBER = 'orderNumber';
    public const ORDER_REF = 'orderReference';
    public const COUPON_CODE = 'couponCode';
    public const PAYMENT_METHOD = 'paymentMethod';
    public const SHIPPING_METHOD = 'billingMethod';
    public const BILLING_ADDRESS = 'billingAddress';
    public const SHIPPING_ADDRESS = 'shippingAddress';
    public const PURCHASE_DATE = 'purchaseDate';
    public const SHIPPING_AMOUNT_INCL_TAX = 'shippingAmountInclTax';
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
            self::ORDER_NUMBER  => 'orderNumber',
            self::ORDER_REF => 'orderReference',
//@todo     'status' => // use status map
            self::COUPON_CODE => 'couponCode',
            self::PAYMENT_METHOD => 'paymentMethod',
            self::SHIPPING_METHOD => 'shippingMethod',
            self::SHIPPING_ADDRESS => 'shippingAddress',
            self::BILLING_ADDRESS => 'billingAddress' ,
            self::SHIPPING_AMOUNT_INCL_TAX => 'shippingAmountInclTax',
            self::TOTAL_TAX => 'totalTax',
            self::DISCOUNT_AMOUNT => 'discount_amount' ,
            self::SUBTOTAL => 'subtotal',
            self::GRAND_TOTAL => 'grand_total',
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
