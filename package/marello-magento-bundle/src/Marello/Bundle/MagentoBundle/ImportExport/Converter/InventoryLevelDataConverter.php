<?php
namespace Marello\Bundle\MagentoBundle\ImportExport\Converter;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Converter\DefaultDataConverter;

use Marello\Bundle\MagentoBundle\Entity\Product;
use Marello\Bundle\MagentoBundle\Provider\EntityManagerTrait;

class InventoryLevelDataConverter extends DefaultDataConverter
{
    use EntityManagerTrait;

    public function __construct(EntityManager $entityManager)
    {
        $this->setEntityManager($entityManager);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $result = parent::convertToExportFormat($exportedRecord, $skipNullValues);

        $qty = (float)$result['balancedInventory'];
        $package = [
            'productId' => $this->getProductOrigin($result['product:sku']),
            'data' => [
                'qty' => $qty,
                'is_in_stock' => $qty > 0 ? 1 : 0,
                'manage_stock' => 1
            ]
        ];

        return $package;
    }

    /**
     * @param $sku
     * @return mixed
     */
    protected function getProductOrigin($sku)
    {
        $search = ['sku' => $sku];

        /**
         * @var $product Product
         */
        $product = $this->getEntityManager()->getRepository('MarelloMagentoBundle:Product')->findOneBy($search);

        if ($product) {
            return $product->getOriginId();
        }

        return false;
    }
}
