<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Writer;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\Magento2Bundle\Entity\Product as InternalMagentoProduct;
use Marello\Bundle\Magento2Bundle\ImportExport\Message\SimpleProductUpdateMessage;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;

class ProductExportUpdateWriter extends AbstractExportWriter
{
    /**
     * @param SimpleProductUpdateMessage $item
     */
    protected function doWrite($item): void
    {
        if (!$item instanceof SimpleProductUpdateMessage) {
            $this->logger->warning(
                '[Magento 2] Given incorrect input data for writing in ProductExportUpdateWriter.',
                [
                    'expected' => SimpleProductUpdateMessage::class,
                    'given' => is_object($item) ? get_class($item) : gettype($item)
                ]
            );

            return;
        }

        /** @var EntityManager $em */
        $internalProductEm = $this->registry->getManagerForClass(InternalMagentoProduct::class);
        /** @var InternalMagentoProduct $internalMagentoProduct */
        $internalMagentoProduct = $internalProductEm->find(
            InternalMagentoProduct::class,
            $item->getInternalMagentoProductId()
        );

        $this->logger->info(
            '[Magento 2] Starting update product.',
            [
                'product_id' => $item->getProductId()
            ]
        );

        try {
            $responseData = $this->getTransport()->updateProduct(
                $item->getProductSku(),
                $item->getPayload()
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
            '[Magento 2] Product was successfully updated.',
            [
                'product_id' => $item->getProductId()
            ]
        );

        if (!empty($responseData['sku'])) {
            $internalMagentoProduct->setSku($responseData['sku']);
        } else {
            $this->logger->warning(
                '[Magento 2] Response of updating product doesn\'t contain valid SKU.',
                [
                    'responseData' => $responseData
                ]
            );
        }

        $internalMagentoProduct->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $internalProductEm->persist($internalMagentoProduct);
        $internalProductEm->flush($internalMagentoProduct);
    }
}
