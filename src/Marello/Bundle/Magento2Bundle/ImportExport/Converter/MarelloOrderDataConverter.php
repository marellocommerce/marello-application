<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class MarelloOrderDataConverter extends AbstractTreeDataConverter
{
    public const ORDER_REF = 'orderReference';
    public const COUPON_CODE = 'couponCode';
    public const PAYMENT_METHOD = 'paymentMethod';
    public const SHIPPING_METHOD = 'billingMethod';
    public const BILLING_ADDRESS = 'billingAddress';
    public const SHIPPING_ADDRESS = 'shippingMethod';
    public const PURCHASE_DATE = 'purchaseDate';
    public const SHIPPING_AMOUNT_INCL_TAX = 'shippingAmountInclTax';
    public const TOTAL_TAX = 'totalTax';
    public const DISCOUNT_AMOUNT = 'discountAmount';
    public const SUBTOTAL = 'subtotal';
    public const GRAND_TOTAL = 'grandTotal';

    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'orderReference' => self::ORDER_REF,
//            'status' => // use status map
            'couponCode' => self::COUPON_CODE,
            'paymentMethod' => self::PAYMENT_METHOD,
            'shippingMethod' => self::SHIPPING_METHOD,
            'shippingAddress' => self::SHIPPING_ADDRESS,
            'billingAddress' => self::BILLING_ADDRESS,
            'shippingAmountInclTax' => self::SHIPPING_AMOUNT_INCL_TAX,
            'totalTax' => self::TOTAL_TAX,
            'discount_amount' => self::DISCOUNT_AMOUNT,
            'subtotal'  => self::SUBTOTAL,
            'grand_total' => self::GRAND_TOTAL,
            'purchaseDate' => self::PURCHASE_DATE
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if ($this->context && $this->context->hasOption('salesChannel')) {
            $convertedResult['salesChannel:id'] = $this->context->getOption('salesChannel');
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
