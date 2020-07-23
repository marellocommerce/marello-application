<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Writer;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\Magento2Bundle\Entity\Product as InternalMagentoProduct;
use Marello\Bundle\Magento2Bundle\ImportExport\Message\SimpleProductUpdateWebsiteScopeMessage;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;

class ProductExportUpdateWebsiteScopeDataWriter extends AbstractExportWriter
{
    /**
     * @param SimpleProductUpdateWebsiteScopeMessage $item
     */
    protected function doWrite($item): void
    {
        if (!$item instanceof SimpleProductUpdateWebsiteScopeMessage) {
            $this->logger->warning(
                '[Magento 2] Given incorrect input data for writing in ProductExportUpdateWebsiteScopeDataWriter.',
                [
                    'expected' => SimpleProductUpdateWebsiteScopeMessage::class,
                    'given' => is_object($item) ? get_class($item) : gettype($item)
                ]
            );

            return;
        }

        /** @var EntityManager $em */
        $internalProductEm = $this->registry->getManagerForClass(InternalMagentoProduct::class);
        $internalMagentoProduct = $internalProductEm->find(
            InternalMagentoProduct::class,
            $item->getInternalMagentoProductId()
        );

        $this->logger->info(
            '[Magento 2] Starting update product website scope data.',
            [
                'product_id' => $item->getProductId(),
                'website_id' => $item->getWebsiteId(),
            ]
        );

        try {
            $responseData = $this->getTransport()->updateProduct(
                $item->getProductSku(),
                $item->getPayload(),
                $item->getStoreCode()
            );
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

        $this->logger->info(
            '[Magento 2] Update product website scope data was successfully finished.',
            [
                'product_id' => $item->getProductId(),
                'website_id' => $item->getWebsiteId(),
            ]
        );

        $internalMagentoProduct->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $internalProductEm->persist($internalMagentoProduct);
        $internalProductEm->flush($internalMagentoProduct);
    }
}
