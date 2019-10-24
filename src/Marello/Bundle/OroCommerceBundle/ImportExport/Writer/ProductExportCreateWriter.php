<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\OroCommerceBundle\Event\RemoteProductCreatedEvent;
use Marello\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductExportCreateWriter extends AbstractProductExportWriter
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
    
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->createProduct($data);

        if (isset($response['data']) && isset($response['data']['type']) && isset($response['data']['id']) &&
            $response['data']['type'] === 'products') {
            $em = $this->registry->getManagerForClass(Product::class);
            $sku = $response['data']['attributes']['sku'];
            $channelId = $this->channel->getId();
            /** @var Product $processedProduct */
            $processedProduct = $em
                ->getRepository(Product::class)
                ->findOneBy(['sku' => $sku]);

            $this->processTaxCode($response);

            if ($processedProduct) {
                $productData = $processedProduct->getData();
                $productData[self::PRODUCT_ID_FIELD][$channelId] = $response['data']['id'];
                $productData[self::UNIT_PRECISION_ID_FIELD][$channelId] =
                    $response['data']['relationships']['primaryUnitPrecision']['data']['id'];
                $productData[self::INVENTORY_LEVEL_ID_FIELD][$channelId] =
                    $response['data']['relationships']['inventoryLevel']['data']['id'];
                $processedProduct->setData($productData);

                $em->persist($processedProduct);
                $em->flush();

                if ($this->eventDispatcher) {
                    $salesChannel = null;
                    foreach ($processedProduct->getChannels() as $sChannel) {
                        if ($sChannel->getIntegrationChannel() &&
                            $sChannel->getIntegrationChannel()->getId() === $channelId) {
                            $salesChannel = $sChannel;
                        }
                    }
                    $this->eventDispatcher->dispatch(
                        RemoteProductCreatedEvent::NAME,
                        new RemoteProductCreatedEvent($processedProduct, $salesChannel)
                    );
                }
            }
            $this->context->incrementAddCount();
        }
    }
}
