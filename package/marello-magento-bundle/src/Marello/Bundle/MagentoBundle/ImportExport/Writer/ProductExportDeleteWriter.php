<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Writer;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\MagentoBundle\Entity\Product;

class ProductExportDeleteWriter extends AbstractExportWriter
{
    const CONTEXT_POST_PROCESS_KEY = 'postProcessProductExport';
    const PRODUCT_SKU = 'sku';

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $item = reset($items);

        if (!$item || !isset($item['productId'])) {
            $this->logger->error('Wrong Product data', (array)$item);

            return;
        }

        $this->transport->init($this->getChannel()->getTransport());

        $this->writeDeleteItem($item);
    }

    /**
     * @param array $item
     */
    protected function writeDeleteItem(array $item)
    {
        try {
            $productId = $item['productId'];

            $productData = $this->transport->deleteProduct(['productId' => $productId]);

            if ($productData) {
                $this->stepExecution->getJobExecution()
                    ->getExecutionContext()
                    ->put(self::CONTEXT_POST_PROCESS_KEY, [$productData]);

                $this->logger->info(
                    sprintf(
                        'Product with id %s and data %s successfully deleted',
                        $productId,
                        json_encode($productData)
                    )
                );
                $this->removeProduct($productId);
            } else {
                $this->logger->error(
                    sprintf(
                        'Product with id %s and data %s was not deleted',
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
     * @param $productId
     * @return $this
     */
    public function removeProduct($productId)
    {
        /** @var ObjectManager $em */
        $em = $this->registry->getManagerForClass(Product::class);

        $product = $em->getRepository(Product::class)->findOneBy(['originId' => $productId]);
        if ($product) {
            $this->logger->info(
                sprintf(
                    'Product with id %s successfully removed from internal reference',
                    $productId
                )
            );
            $em->remove($product);
        }

        return $this;
    }
}
