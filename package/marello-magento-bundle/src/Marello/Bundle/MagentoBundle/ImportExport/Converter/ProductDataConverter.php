<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Converter;

use Doctrine\ORM\EntityManager;

use Marello\Bundle\MagentoBundle\Entity\Product;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

use Marello\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Marello\Bundle\MagentoBundle\Provider\EntityManagerTrait;

class ProductDataConverter extends IntegrationAwareDataConverter
{
    const PRODUCT_TYPE_SIMPLE = 'simple';
    const PRODUCT_TYPE_CONFIGURABLE = 'configurable';
    const DEFAULT_ATTRIBUTE_SET_ID = 4;
    const DEFAULT_CATALOG_VISIBILITY = 4;
    const DEFAULT_TAX_CLASS_ID = 2;
    const DEFAULT_PRODUCT_STATUS = 2; //disabled in magento


    protected $mageStatus = [ 'enabled' => 1, 'disabled' => 2];

    use EntityManagerTrait;

    public function __construct(EntityManager $entityManager)
    {
        $this->setEntityManager($entityManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'product_id'        => 'originId',
            'sku'               => 'sku',
            'name'              => 'name',
            'type'              => 'type',
            'price'             => 'price',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $result = parent::convertToImportFormat($importedRecord, $skipNullValues);

        $dateObj = new \DateTime('now', new \DateTimeZone('UTC'));
        $date = $dateObj->format('Y-m-d H:i:s');
        $result['createdAt'] = $date;
        $result['updatedAt'] = $date;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $sku = $exportedRecord['sku'];
        $name = $exportedRecord['name'];
        $productData = [
            'weight'            => $exportedRecord['weight'],
            'status'            => self::DEFAULT_PRODUCT_STATUS,
            'visibility'        => self::DEFAULT_CATALOG_VISIBILITY,
            'name'              => $name,
            'description'       => $name,
            'short_description' => $name,
            'tax_class_id'      => self::DEFAULT_TAX_CLASS_ID,
            'website_ids'       => $this->getWebsiteIds()
        ];

        $result = [
            'productData' => $productData,
            'sku' => $sku,
            'set' => self::DEFAULT_ATTRIBUTE_SET_ID,
            'type' => self::PRODUCT_TYPE_SIMPLE
        ];

        /**
         * magento productId - update
         */
        if ($productId = $this->getProductOrigin($sku)) {
            $result['productId'] = $productId;

            $updateNotSupported = [
                'status',
                'visibility',
                'description',
                'short_description',
            ];

            foreach ($updateNotSupported as $code) {
                unset($result['productData'][$code]);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }

    public function getWebsiteIds()
    {
        $integrationChannel = $this->getIntegrationChannel();

        /** @var MagentoSoapTransport $transport */
        $transport = $integrationChannel->getTransport();

        $websiteId = $transport->getWebsiteId();
        $websiteIds = [];
        switch ($websiteId) {
            default:
                foreach ($transport->getWebsites() as $website) {
                    if ($website['id'] >= 1) {
                        $websiteIds[] = $website['id'];
                    }
                }
                break;
        }
        return $websiteIds;
    }

    /**
     * @param $sku
     * @return int
     * @throws \Exception
     */
    protected function getProductOrigin($sku)
    {
        $search = ['sku' => $sku];

        /**
         * @var $product Product
         */
        $product = $this->getEntityManager()->getRepository('MarelloMagentoBundle:Product')->findOneBy($search);

        if ($product) {
            return $product->getOriginId();
        }

        return false;
    }

    /**
     * @param $integrationChannelId
     * @return Channel
     */
    protected function getIntegrationChannel()
    {
        return $this->getEntityManager()
            ->getRepository(Channel::class)
            ->findOneBy(['id' => $this->context->getOption('channel')]);
    }
}
