<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Hydrator;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Marello\Bundle\Magento2Bundle\DTO\OrderIdentifierDTO;

class OrderIdentifierDTOHydrator extends AbstractHydrator
{
    /**
     * {@inheritDoc}
     */
    protected function hydrateAllData()
    {
        $result = [];
        $mappings = \array_flip($this->_rsm->scalarMappings);
        while ($row = $this->_stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = new OrderIdentifierDTO(
                $row[$mappings['marelloOrderId']],
                $row[$mappings['magentoOrderId']]
            );
        }

        return $result;
    }
}
