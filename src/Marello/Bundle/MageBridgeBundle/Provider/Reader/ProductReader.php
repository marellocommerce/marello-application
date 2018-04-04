<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 03/04/2018
 * Time: 14:56
 */

namespace Marello\Bundle\MageBridgeBundle\Provider\Reader;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

class ProductReader extends \Oro\Bundle\ImportExportBundle\Reader\EntityReader
{
    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        //TODO: filter product based on sales channel related to the magento intergration

        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);
        return $qb;
    }
}