<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class OroCommerceIntegrationEventListener
{
    const PGSQL_DRIVER = 'pdo_pgsql';
    const MYSQL_DRIVER = 'pdo_mysql';

    /**
     * @var string
     */
    private $databaseDriver;

    /**
     * @param string $databaseDriver
     */
    public function __construct($databaseDriver)
    {
        $this->databaseDriver = $databaseDriver;
    }

    /**
     * @param Channel $channel
     * @param LifecycleEventArgs $args
     */
    public function postPersist(Channel $channel, LifecycleEventArgs $args)
    {
        if ($channel->getType() === OroCommerceChannelType::TYPE) {
            $em = $args->getEntityManager();
            $existingSalesChannels = $em
                ->getRepository(SalesChannel::class)
                ->findBy(['integrationChannel' => $channel]);

            /** @var SalesChannel $existingSalesChannel */
            // remove integration from the previous saleschannels
            foreach ($existingSalesChannels as $existingSalesChannel) {
                $existingSalesChannel->setIntegrationChannel(null);
                $em->persist($existingSalesChannel);
            }

            /** @var OroCommerceSettings $transport */
            $transport = $channel->getTransport();
            $salesChannelGroup = $transport->getSalesChannelGroup();
            if ($salesChannelGroup) {
                foreach ($salesChannelGroup->getSalesChannels() as $salesChannel) {
                    $salesChannel->setIntegrationChannel($channel);
                    $em->persist($salesChannel);
                }
            }
            $em->flush();
        }
    }

    /**
     * @param Channel $channel
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(Channel $channel, LifecycleEventArgs $args)
    {
        if ($channel->getType() === OroCommerceChannelType::TYPE) {
            $em = $args->getEntityManager();
            $existingSalesChannels = $em
                ->getRepository(SalesChannel::class)
                ->findBy(['integrationChannel' => $channel]);
            /** @var SalesChannel $existingSalesChannel */
            // remove integration from the previous saleschannels
            foreach ($existingSalesChannels as $existingSalesChannel) {
                $existingSalesChannel->setIntegrationChannel(null);
                $em->persist($existingSalesChannel);
            }

            /** @var OroCommerceSettings $transport */
            $transport = $channel->getTransport();
            $salesChannelGroup = $transport->getSalesChannelGroup();
            foreach ($salesChannelGroup->getSalesChannels() as $salesChannel) {
                $salesChannel->setIntegrationChannel($channel);
                $em->persist($salesChannel);
            }
            $em->flush();
        }
    }

    /**
     * @param Channel $channel
     * @param LifecycleEventArgs $args
     */
    public function preRemove(Channel $channel, LifecycleEventArgs $args)
    {
        if ($channel->getType() === OroCommerceChannelType::TYPE) {
            $em = $args->getEntityManager();
            $salesChannels = $em
                ->getRepository(SalesChannel::class)
                ->findBy(['integrationChannel' => $channel]);
            /** @var SalesChannel $salesChannel */
            foreach ($salesChannels as $salesChannel) {
                $salesChannel->setIntegrationChannel(null);
                $em->getUnitOfWork()->scheduleForUpdate($salesChannel);
            }

            $section = AbstractProductExportWriter::SECTION_FIELD;
            if ($this->databaseDriver === self::PGSQL_DRIVER) {
                $formattedDataField = 'CAST(p.data as TEXT)';
            } else {
                $formattedDataField = 'p.data';
            }
            $qb = $em->createQueryBuilder();
            $qb
                ->select('p')
                ->from('MarelloProductBundle:Product', 'p')
                ->where(sprintf('%s LIKE :section', $formattedDataField))
                ->setParameter('section', '%' . $section . '%');
            /** @var Product[] $products */
            $products = $qb->getQuery()->getResult();

            $existingOroCommerceChannels = $em->getRepository(Channel::class)
                ->findBy(['type' => OroCommerceChannelType::TYPE]);
            $existingChannels = [];
            foreach ($existingOroCommerceChannels as $existingOroCommerceChannel) {
                if ($existingOroCommerceChannel->getId() !== $channel->getId()) {
                    $existingChannels[] = $existingOroCommerceChannel->getId();
                }
            }

            foreach ($products as $product) {
                $productData = $product->getData();
                $productData = $this->unsetProductData(
                    $productData,
                    AbstractProductExportWriter::PRODUCT_ID_FIELD,
                    $existingChannels
                );
                $productData = $this->unsetProductData(
                    $productData,
                    AbstractProductExportWriter::PRICE_ID_FIELD,
                    $existingChannels
                );
                $productData = $this->unsetProductData(
                    $productData,
                    AbstractProductExportWriter::UNIT_PRECISION_ID_FIELD,
                    $existingChannels
                );
                $productData = $this->unsetProductData(
                    $productData,
                    AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD,
                    $existingChannels
                );
                $productData = $this->unsetProductData(
                    $productData,
                    AbstractProductExportWriter::IMAGE_ID_FIELD,
                    $existingChannels
                );
                $product->setData($productData);

                $em->getUnitOfWork()->scheduleForUpdate($product);
            }
        }
    }

    /**
     * @param array $productData
     * @param string $key
     * @param array $existingChannels
     * @return array
     */
    private function unsetProductData($productData, $key, array $existingChannels)
    {
        foreach ($productData[$key] as $channelId => $channelData) {
            if (!in_array($channelId, $existingChannels)) {
                unset($productData[$key][$channelId]);
            }
        }
        if (empty($productData[$key])) {
            unset($productData[$key]);
        }

        return $productData;
    }
}
