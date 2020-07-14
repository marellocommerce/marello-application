<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;
use Oro\Component\PropertyAccess\PropertyAccessor;

class MagentoOrderDataConverter extends AbstractTreeDataConverter
{
    public const ID_COLUMN_NAME = 'entity_id';
    public const CREATED_AT_COLUMN_NAME = 'created_at';
    public const UPDATED_AT_COLUMN_NAME = 'updated_at';
    public const UPDATED_AT_COLUMN_FORMAT = 'Y-m-d H:i:s';
    public const STORE_ID_COLUMN_NAME = 'store_id';

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = new PropertyAccessor(false, true);
    }

    /**
     * Marks property paths that must be accessible by short alias,
     * combines data by keys and put it under single alias key to process it with branch converter
     *
     * @var array
     */
    protected $aliasesToPaths = [
        'customer_data_alias' => [
            CustomerDataConverter::ORIGIN_ID => 'customer_id',
            CustomerDataConverter::EMAIL => 'customer_email',
            CustomerDataConverter::FIRST_NAME => 'customer_firstname',
            CustomerDataConverter::LAST_NAME => 'customer_lastname',
        ],
        'marello_order_data_alias' => [
            MarelloOrderDataConverter::ORDER_REF => 'increment_id',
            MarelloOrderDataConverter::COUPON_CODE => 'coupon_code',
            MarelloOrderDataConverter::BILLING_ADDRESS => 'billing_address',
            MarelloOrderDataConverter::PAYMENT_METHOD => 'payment:method',
            MarelloOrderDataConverter::SHIPPING_ADDRESS =>
                'extension_attributes:shipping_assignments:0:shipping:address',
            MarelloOrderDataConverter::SHIPPING_METHOD => 'extension_attributes:shipping_assignments:0:shipping:method',
            MarelloOrderDataConverter::PURCHASE_DATE => 'created_at',
            MarelloOrderDataConverter::SHIPPING_AMOUNT_INCL_TAX => 'shipping_incl_tax',
            MarelloOrderDataConverter::TOTAL_TAX => 'tax_amount',
            MarelloOrderDataConverter::DISCOUNT_AMOUNT => 'discount_amount',
            MarelloOrderDataConverter::SUBTOTAL => 'subtotal',
            MarelloOrderDataConverter::GRAND_TOTAL => 'grand_total'
        ]
    ];

    /**
     * Describe data-schema:
     *
     * Magento Order -> Magento Customer
     *  |               |
     * Order  -> Customer
     * |
     * Order Items  | Shipping Address | Billing Address
     */

    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'originId' => self::ID_COLUMN_NAME,
            'magentoCustomer' => 'customer_data_alias',
            'innerOrder' => 'marello_order_data_alias',
            'createdAt' => self::CREATED_AT_COLUMN_NAME,
            'updatedAt' => self::UPDATED_AT_COLUMN_NAME
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $this->copyDataByPathToAlias($importedRecord);

        if (!empty($importedRecord[self::STORE_ID_COLUMN_NAME])) {
            $importedRecord['store:id'] = $importedRecord[self::STORE_ID_COLUMN_NAME];
        }

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * @param array $importedRecord
     */
    protected function copyDataByPathToAlias(array &$importedRecord): void
    {
        foreach ($this->aliasesToPaths as $alias => $path) {
            if (is_array($path)) {
                $importedRecord[$alias] = \array_map([$this, 'copyDataByPathToAlias'], $path);
            } else {
                $importedRecord[$alias] = $this->propertyAccessor->getValue(
                    $importedRecord,
                    $path
                );
            }
        }
    }

    /**
     * @return array|void
     */
    protected function getBackendHeader()
    {
        throw new RuntimeException('Normalization is not implemented!');
    }
}
