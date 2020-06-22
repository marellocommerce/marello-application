<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;

class UpdateInventoryEntitiesConfig implements Migration
{
    const CONFIG_ATTRIBUTE = 'auditable';
    const CONFIG_SCOPE = 'dataaudit';

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateInventoryLevelEntityConfig($queries);
        $this->updateBalancedInventoryLevelEntityConfig($queries);
    }

    /**
     * @param QueryBag $queries
     */
    protected function updateInventoryLevelEntityConfig(QueryBag $queries)
    {
        $queries->addPostQuery(
            new UpdateEntityConfigEntityValueQuery(
                InventoryLevel::class,
                self::CONFIG_SCOPE,
                self::CONFIG_ATTRIBUTE,
                0
            )
        );

        $fields = [
            'inventoryItem',
            'warehouse',
            'inventory',
            'allocatedInventory',
            'createdAt',
            'updatedAt',
            'managedInventory',
            'organization'
        ];

        foreach ($fields as $fieldName) {
            $queries->addPostQuery(
                new UpdateEntityConfigFieldValueQuery(
                    InventoryLevel::class,
                    $fieldName,
                    self::CONFIG_SCOPE,
                    self::CONFIG_ATTRIBUTE,
                    0
                )
            );
        }
    }

    /**
     * @param QueryBag $queries
     */
    protected function updateBalancedInventoryLevelEntityConfig(QueryBag $queries)
    {
        $queries->addPostQuery(
            new UpdateEntityConfigEntityValueQuery(
                BalancedInventoryLevel::class,
                self::CONFIG_SCOPE,
                self::CONFIG_ATTRIBUTE,
                0
            )
        );

        $fields = [
            'product',
            'salesChannelGroup',
            'inventory',
            'balancedInventory',
            'reservedInventory',
            'createdAt',
            'updatedAt',
            'organization'
        ];

        foreach ($fields as $fieldName) {
            $queries->addPostQuery(
                new UpdateEntityConfigFieldValueQuery(
                    BalancedInventoryLevel::class,
                    $fieldName,
                    self::CONFIG_SCOPE,
                    self::CONFIG_ATTRIBUTE,
                    0
                )
            );
        }
    }
}
