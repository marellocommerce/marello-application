<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Writer;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\Magento2Bundle\Entity\Product as InternalMagentoProduct;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;

class ProductExportUpdateWriter extends AbstractExportWriter
{
    /**
     * @param $item
     * @return mixed|void
     */
    protected function doWrite($item)
    {
        $productIdsWithInternalProductIds = $this->stepExecution
                ->getJobExecution()
                ->getExecutionContext()
                ->get(InternalMagentoProductWriter::INTERNAL_MAGENTO_PRODUCT_IDS_CONTEXT) ?? [];

        /** @var InternalMagentoProduct $internalMagentoProduct */
        $internalMagentoProductId = $productIdsWithInternalProductIds[$item['productId']];

        /** @var EntityManager $em */
        $internalProductEm = $this->registry->getManagerForClass(InternalMagentoProduct::class);
        $internalMagentoProduct = $internalProductEm->getReference(
            InternalMagentoProduct::class,
            $internalMagentoProductId
        );

        try {
            $responseData = $this->getTransport()->updateProduct($item['sku'], $item['payload']);
        } catch (RestException $restException) {

            $className = ExtendHelper::buildEnumValueClassName(InternalMagentoProduct::STATUS_CODE);
            $readyStatus = $internalProductEm
                ->getRepository($className)
                ->find(ExtendHelper::buildEnumValueId(InternalMagentoProduct::STATUS_SYNC_ISSUE));

            $internalMagentoProduct->setStatus($readyStatus);
            $internalProductEm->persist($internalMagentoProduct);
            $internalProductEm->flush($internalMagentoProduct);

            throw $restException;
        }

        $internalMagentoProduct->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $internalProductEm->persist($internalMagentoProduct);
        $internalProductEm->flush($internalMagentoProduct);
    }
}
