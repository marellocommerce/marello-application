<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_4_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;

class UpdateInventoryItemTable implements Migration, ExtendExtensionAwareInterface
{
    const INVENTORY_ITEM_TABLE_NAME = 'marello_inventory_item';
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateInventoryItemTable($schema);
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     * @param QueryBag $queries
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateInventoryItemTable(Schema $schema)
    {
        $table = $schema->getTable(self::INVENTORY_ITEM_TABLE_NAME);
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'unitOfMeasurement',
            'marello_product_unit',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );

    }

    /**
     * Sets the ExtendExtension
     *
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
