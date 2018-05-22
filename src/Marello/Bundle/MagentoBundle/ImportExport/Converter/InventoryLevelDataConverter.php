<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 18/05/2018
 * Time: 09:22
 */

namespace Marello\Bundle\MagentoBundle\ImportExport\Converter;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ImportExportBundle\Converter\DefaultDataConverter;

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

        $package = [
            'productId' => $originId,
            'data' => [
                'qty' => $result['balancedInventory']
            ]
        ];

        return $package;
    }
}
