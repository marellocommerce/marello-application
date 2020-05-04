<?php
namespace Marello\Bundle\MagentoBundle\ImportExport\Writer;

class ProductCategoryExportWriter extends AbstractExportWriter
{
    const CONTEXT_POST_PROCESS_KEY = 'postProcessProductCategoryExport';

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $item = reset($items);

        if (!$item) {
            $this->logger->error('Wrong category product assignment data', (array)$item);

            return;
        }

        $categoryId = $item['categoryId'];

        $this->transport->init($this->getChannel()->getTransport());

        $assignedProducts = $this->transport->catalogCategoryAssignedProducts($categoryId);

        $mageAssignedProductIds = array_keys($assignedProducts);

        $removedLinks = array_diff($mageAssignedProductIds, $item['products']);

        /**
         * un-assign these category links
         */
        foreach ($removedLinks as $removedLinkedProduct) {
            $this->removeExistingItem(['categoryId' => $categoryId, 'productId' => $removedLinkedProduct]);
        }

        /**
         * assign category links
         */
        foreach ($item['products'] as $productId) {
            if (array_key_exists($productId, $mageAssignedProductIds)) {
                $this->logger->debug("link exist catid: {$categoryId} / prodId {$productId}");
                continue;
            }
            $this->writeExistingItem(['categoryId' => $categoryId, 'productId' => $productId]);
        }
    }

    /**
     * @param array $item
     */
    protected function writeExistingItem(array $item)
    {
        try {
            $productId = $item['productId'];
            $result = $this->transport->catalogCategoryAssignProduct($item);

            if ($result) {
                $this->stepExecution->getJobExecution()
                    ->getExecutionContext()
                    ->put(self::CONTEXT_POST_PROCESS_KEY, [$item]);

                $this->logger->info(
                    sprintf(
                        'Product with id %s and data %s successfully updated',
                        $productId,
                        json_encode($item)
                    )
                );
            } else {
                $this->logger->error(
                    sprintf(
                        'Product with id %s and data %s was not updated',
                        $productId,
                        json_encode($item)
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }
    }

    /**
     * @param array $item
     */
    protected function removeExistingItem(array $item)
    {
        try {
            $productId = $item['productId'];
            $categoryId = $item['categoryId'];
            $result = $this->transport->catalogCategoryRemoveProduct($categoryId, $productId);

            if ($result) {
                $this->stepExecution->getJobExecution()
                    ->getExecutionContext()
                    ->put(self::CONTEXT_POST_PROCESS_KEY, [$item]);

                $this->logger->info(
                    sprintf(
                        'Product with id %s and categoryID %s successfully updated',
                        $productId,
                        $categoryId
                    )
                );
            } else {
                $this->logger->error(
                    sprintf(
                        'Product with id %s and categoryID %s was not updated',
                        $productId,
                        $categoryId
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }
    }
}
