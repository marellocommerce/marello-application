<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Writer;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\Magento2Bundle\Entity\Product as InternalMagentoProduct;
use Marello\Bundle\Magento2Bundle\ImportExport\Message\SimpleProductCreateMessage;
use Marello\Bundle\Magento2Bundle\Scheduler\ProductSchedulerInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;

class ProductExportCreateWriter extends AbstractExportWriter
{
    /** @var ProductSchedulerInterface */
    protected $productScheduler;

    /**
     * @param ProductSchedulerInterface $productScheduler
     */
    public function setProductScheduler(ProductSchedulerInterface $productScheduler)
    {
        $this->productScheduler = $productScheduler;
    }

    /**
     * @param SimpleProductCreateMessage $item
     */
    protected function doWrite($item): void
    {
        if (!$item instanceof SimpleProductCreateMessage) {
            $this->logger->warning(
                '[Magento 2] Given incorrect input data for writing in ProductExportCreateWriter.',
                [
                    'expected' => SimpleProductCreateMessage::class,
                    'given' => is_object($item) ? get_class($item) : gettype($item)
                ]
            );

            return;
        }

        $responseData = $this->getTransport()->createProduct($item->getPayload());

        /** @var EntityManager $productEm */
        $productEm = $this->registry->getManagerForClass(Product::class);
        /** @var Product $product */
        $product = $productEm->getReference(Product::class, $item->getProductId());

        /** @var EntityManager $em */
        $internalProductEm = $this->registry->getManagerForClass(InternalMagentoProduct::class);
        $enumClassName = ExtendHelper::buildEnumValueClassName(InternalMagentoProduct::STATUS_CODE);
        $readyStatus = $internalProductEm
            ->getRepository($enumClassName)
            ->find(ExtendHelper::buildEnumValueId(InternalMagentoProduct::STATUS_READY));

        /** @var InternalMagentoProduct $internalMagentoProduct */
        $internalMagentoProduct = new InternalMagentoProduct();
        $internalMagentoProduct->setOriginId($responseData['id']);
        $internalMagentoProduct->setChannel($this->getChannel());
        $internalMagentoProduct->setProduct($product);
        $internalMagentoProduct->setStatus($readyStatus);

        if (empty($responseData['sku'])) {
            $internalMagentoProduct->setSku($product->getSku());
            $this->logger->warning(
                '[Magento 2] Response of creating product doesn\'t contain valid SKU.',
                [
                    'responseData' => $responseData
                ]
            );
        } else {
            $internalMagentoProduct->setSku($responseData['sku']);
        }

        $internalProductEm->persist($internalMagentoProduct);
        $internalProductEm->flush($internalMagentoProduct);

        $this->scheduleSyncOfWebsiteData($item);
    }

    /**
     * @param SimpleProductCreateMessage $item
     */
    protected function scheduleSyncOfWebsiteData(SimpleProductCreateMessage $item): void
    {
        foreach ($item->getWebsiteIds() as $websiteId) {
            $this->productScheduler
                ->scheduleUpdateWebsiteScopeDataProductsOnChannel(
                    $this->getChannel()->getId(),
                    $websiteId,
                    [$item->getProductId()]
                );
        }
    }
}
