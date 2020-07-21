<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Marello\Bundle\Magento2Bundle\Converter\OrderStatusIdConverterInterface;
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

    private const MARELLO_STATUS_COLUMN_NAME = 'marello_status';

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /** @var OrderStatusIdConverterInterface */
    protected $orderStatusIdConverter;

    /**
     * @param OrderStatusIdConverterInterface $orderStatusIdConverter
     */
    public function __construct(OrderStatusIdConverterInterface $orderStatusIdConverter)
    {
        $this->propertyAccessor = new PropertyAccessor(false, true);
        $this->orderStatusIdConverter = $orderStatusIdConverter;
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
            MarelloOrderDataConverter::ORDER_STATUS_ID => self::MARELLO_STATUS_COLUMN_NAME,
            MarelloOrderDataConverter::COUPON_CODE => 'coupon_code',
            MarelloOrderDataConverter::BILLING_ADDRESS => 'billing_address',
            MarelloOrderDataConverter::PAYMENT_METHOD => 'payment.method',
            MarelloOrderDataConverter::SHIPPING_ADDRESS =>
                'extension_attributes.shipping_assignments[0].shipping.address',
            MarelloOrderDataConverter::SHIPPING_METHOD =>
                'extension_attributes.shipping_assignments[0].shipping.method',
            MarelloOrderDataConverter::PURCHASE_DATE => 'created_at',
            MarelloOrderDataConverter::SHIPPING_AMOUNT_INCL_TAX => 'shipping_incl_tax',
            MarelloOrderDataConverter::TOTAL_TAX => 'tax_amount',
            MarelloOrderDataConverter::DISCOUNT_AMOUNT => 'discount_amount',
            MarelloOrderDataConverter::SUBTOTAL => 'subtotal',
            MarelloOrderDataConverter::GRAND_TOTAL => 'grand_total',
            MarelloOrderDataConverter::ITEMS => 'items'
        ]
    ];

    /**
     * Data-schema:
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
            self::ID_COLUMN_NAME => 'originId',
            'customer_data_alias' => 'magentoCustomer',
            'marello_order_data_alias' => 'innerOrder',
            self::CREATED_AT_COLUMN_NAME => 'createdAt',
            self::UPDATED_AT_COLUMN_NAME => 'updatedAt'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord[self::MARELLO_STATUS_COLUMN_NAME] = $this->orderStatusIdConverter->convertMagentoStatusId(
            $importedRecord['status'] ?? null
        );

        $importedRecord = $this->copyDataByPathToAlias($importedRecord);

        if (!empty($importedRecord[self::STORE_ID_COLUMN_NAME])) {
            $importedRecord['store:originId'] = $importedRecord[self::STORE_ID_COLUMN_NAME];
        }

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * @param array $importedRecord
     * @return array
     */
    protected function copyDataByPathToAlias(array $importedRecord): array
    {
        foreach ($this->aliasesToPaths as $alias => $path) {
            if (is_array($path)) {
                foreach ($path as $subAlias => $subPath) {
                    $importedRecord[$alias][$subAlias] = $this->propertyAccessor->getValue(
                        $importedRecord,
                        $subPath
                    );
                }
            } else {
                $importedRecord[$alias] = $this->propertyAccessor->getValue(
                    $importedRecord,
                    $path
                );
            }
        }

        return $importedRecord;
    }

    /**
     * @return array|void
     */
    protected function getBackendHeader()
    {
        throw new RuntimeException('Normalization is not implemented!');
    }
}
