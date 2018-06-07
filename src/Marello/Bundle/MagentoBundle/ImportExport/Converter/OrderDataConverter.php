<?php
namespace Marello\Bundle\MagentoBundle\ImportExport\Converter;

use Doctrine\ORM\EntityManager;

use Marello\Bundle\MagentoBundle\Provider\EntityManagerTrait;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class OrderDataConverter extends AbstractTreeDataConverter
{
    use EntityManagerTrait;

    /**
     * OrderDataConverter constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->setEntityManager($entityManager);
    }

    /** @var string[] */
    protected $arrayNodeKeys = [
        'items',
        'status_history'
    ];

    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'increment_id'        => 'orderReference',
            'order_id'            => 'orderNumber',
            'customer_id'         => 'customerId',
            'customer_is_guest'   => 'isGuest',
            'order_currency_code' => 'currency',
            'subtotal'            => 'subtotal',
            'shipping_amount'     => 'shippingAmountInclTax',
            'shipping_method'     => 'shippingMethod',
            'tax_amount'          => 'totalTax',
            'discount_amount'     => 'discountAmount',
            'grand_total'         => 'grandTotal',
            'payment'             => 'paymentMethod',
            'shipping_address'    => 'address:0',
            'billing_address'     => 'address:1',
            'created_at'          => 'createdAt',
            'updated_at'          => 'updatedAt',
            'customer_email'      => 'customerEmail',
            'coupon_code'         => 'couponCode',
            'status_history'      => 'orderNotes'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        foreach ($this->arrayNodeKeys as $key) {
            if (!empty($importedRecord[$key])) {
                $this->fixNodeWithSingleRecord($importedRecord, $key);
            }
        }

        if ($this->context && $this->context->hasOption('channel')) {
            $importedRecord['store:channel:id'] = $this->context->getOption('channel');
            $importedRecord['customer:channel:id'] = $this->context->getOption('channel');
            $importedRecord['cart:channel:id'] = $this->context->getOption('channel');
        }

        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);
        $importedRecord = AttributesConverterHelper::addUnknownAttributes($importedRecord, $this->context);

        if (!empty($importedRecord['paymentMethod']['method'])) {
            $importedRecord['paymentMethod'] = $importedRecord['paymentMethod']['method'];
        } else {
            $importedRecord['paymentMethod'] = null;
        }

        $importedRecord['salesChannel'] = ['id' => $this->getSalesChannel()->getId()];

        $items = [];
        foreach ($importedRecord['items'] as $mageItem) {
            $items[] = $this->prepareOrderItem($mageItem);
        }

        $importedRecord['items'] = $items;

        $importedRecord['customer'] = [
            'organization' => ['id' => 1],
            'firstName' => $importedRecord['customer_firstname'],
            'lastName' => $importedRecord['customer_lastname'],
            'email' => $importedRecord['customerEmail'],
        ];

        $importedRecord['organization'] = ['id' => 1];

        //prepare addresses
        foreach ($importedRecord['address'] as $rawAddress) {
            $type = $rawAddress['address_type']."Address";
            $importedRecord[$type] = $this->prepareAddress($rawAddress);
        }


        return $importedRecord;
    }

    /**
     * @param $address
     * @return array
     */
    protected function prepareAddress($address)
    {
        $format = [
            'firstname'        => 'firstName',
            'middlename'       => 'middleName',
            'lastname'         => 'lastName',
            'telephone'        => 'phone',
            'country_id'       => 'country',
            'region'           => 'regionText',
            'postcode'         => 'postalCode',
        ];
        return $this->prepareEntityData($address, $format);
    }

    /**
     * @param $address
     * @return array
     */
    protected function prepareOrderItem($item)
    {
        $format = [
            'sku'               => 'productSku',
            'name'              => 'productName',
            'qty_ordered'       => 'quantity',
            'tax_amount'        => 'tax',
            'row_total'         => 'rowTotalInclTax',
            'base_row_total'    => 'rowTotalExclTax',
        ];
        $orderItem = $this->prepareEntityData($item, $format);
        return $orderItem;
    }

    /**
     * @param $entity
     * @param array $format
     * @return array
     */
    protected function prepareEntityData($entity, $format = [])
    {
        $entityData = [];
        foreach ($entity as $key => $value) {
            if (!isset($format[$key])) {
                $entityData[$key] = $value;
            } else {
                $entityData[$format[$key]] = $value;
            }
        }
        return $entityData;
    }

    /**
     * @param $sku
     * @return mixed
     */
    protected function getProductBySku($sku)
    {
        return $this->getEntityManager()
            ->getRepository(Product::class)
            ->findOneBy(['sku' => $sku]);
    }

    /**
     * @return mixed
     */
    protected function getSalesChannel()
    {
        return $this->getEntityManager()
            ->getRepository(SalesChannel::class)
            ->findOneBy(['integrationChannel' => $this->context->getOption('channel')]);
    }


    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        // will be implemented for bidirectional sync
        throw new \Exception('Normalization is not implemented!');
    }

    /**
     * @param mixed[]   $importedRecord
     * @param string    $key
     */
    private function fixNodeWithSingleRecord(array &$importedRecord, $key)
    {
        // normalize order rows if single is passed
        if (!empty($importedRecord[$key])) {
            /** @var array $data */
            $data = $importedRecord[$key];
            foreach ($data as $item) {
                if (!is_array($item)) {
                    $importedRecord[$key] = [$data];
                    break;
                }
            }
        }
    }
}
