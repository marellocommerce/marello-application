<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Writer;

use Psr\Log\NullLogger;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Oro\Bundle\BatchBundle\Step\StepExecutionRestoreInterface;
use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;

use Marello\Bundle\MagentoBundle\Entity\Product;

class ProxyEntityWriter implements
    ItemWriterInterface,
    StepExecutionAwareInterface,
    StepExecutionRestoreInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ItemWriterInterface */
    protected $writer;

    /** @var DatabaseHelper */
    protected $databaseHelper;

    /** @var StepExecution|null */
    protected $previousStepExecution;

    /** @var  GuestCustomerStrategyHelper */
    private $guestCustomerStrategyHelper;

    /**
     * @param ItemWriterInterface $writer
     * @param DatabaseHelper $databaseHelper
     */
    public function __construct(ItemWriterInterface $writer, DatabaseHelper $databaseHelper)
    {
        $this->writer = $writer;
        $this->databaseHelper = $databaseHelper;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     *
     * Prepare items for PersistentBatchWriter, filters for duplicates and takes only latest versions
     */
    public function write(array $items)
    {
        $uniqueItems = [];
        $uniqueKeys = [];
        foreach ($items as $item) {
            if ($item instanceof Product) {
                $identifier = $item->getOriginId();
                if (in_array($identifier, $uniqueKeys)) {
                    continue;
                }
                $uniqueItems[] = $item;
                $uniqueKeys[] = $identifier;
            } else {
                $uniqueItems[] = $item;
            }
        }
        $this->writer->write($uniqueItems);

        // force entity cache clear if clear is skipped
        $this->databaseHelper->onClear();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        if ($this->writer instanceof StepExecutionAwareInterface) {
            $this->writer->setStepExecution($stepExecution);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restoreStepExecution()
    {
        if ($this->writer instanceof StepExecutionRestoreInterface) {
            $this->writer->restoreStepExecution();
        }
    }

    /**
     * @param array $uniqueItems
     * @param object $item
     * @param string|null $identifier
     */
    protected function handleIdentifier(array &$uniqueItems, $item, $identifier = null)
    {
        if ($identifier && array_key_exists($identifier, $uniqueItems)) {
            $this->logSkipped($identifier);
        }

        if ($identifier) {
            $uniqueItems[$identifier] = $item;
        } else {
            $uniqueItems[spl_object_hash($item)] = $item;
        }
    }

    /**
     * @param int|string $identifier
     */
    protected function logSkipped($identifier)
    {
        $this->logger->info(
            sprintf('[origin_id=%s] Item skipped because of newer version found', (string)$identifier)
        );
    }
}
