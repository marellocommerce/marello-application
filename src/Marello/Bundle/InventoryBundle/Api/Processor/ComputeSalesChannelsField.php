<?php

namespace Marello\Bundle\InventoryBundle\Api\Processor;

use Doctrine\ORM\EntityManagerInterface;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;

class ComputeSalesChannelsField implements ProcessorInterface
{
    public function __construct(
        protected EntityManagerInterface $manager
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeLoadedDataContext $context */
        $data = $context->getData();

        $salesChannelFieldName = $context->getResultFieldName('saleschannels');
        if (!$context->isFieldRequested($salesChannelFieldName, $data)) {
            return;
        }

        $balancedInventoryLevelFieldName = $context->getResultFieldName('id');
        if (!$balancedInventoryLevelFieldName || empty($data[$balancedInventoryLevelFieldName])) {
            return;
        }

        $data[$salesChannelFieldName] = $this->loadSalesChannels((int)$data[$balancedInventoryLevelFieldName]);
        $context->setData($data);
    }

    /**
     * @param int $balancedInventoryLevelFieldName
     * @return array
     */
    private function loadSalesChannels(int $balancedInventoryLevelFieldName): array
    {
        /** @var BalancedInventoryLevel $level */
        $level = $this->manager
            ->getRepository(BalancedInventoryLevel::class)
            ->find($balancedInventoryLevelFieldName);

        $salesChannelCodes = [];

        if ($level->getSalesChannelGroup()) {
            $salesChannels = $this->manager
                ->getRepository(SalesChannel::class)
                ->findBy(['group' => $level->getSalesChannelGroup()->getId()]);
            /** @var SalesChannel $salesChannel */
            foreach ($salesChannels as $salesChannel) {
                $salesChannelCodes[] = $salesChannel->getCode();
            }
        }

        return $salesChannelCodes;
    }
}
