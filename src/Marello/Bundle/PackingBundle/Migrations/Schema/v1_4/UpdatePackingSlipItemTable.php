<?php

namespace Marello\Bundle\PackingBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;

class UpdatePackingSlipItemTable implements Migration, ExtendExtensionAwareInterface
{
    const MARELLO_PACKING_SLIP_ITEM_TABLE = 'marello_packing_pack_slip_item';
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updatePackingSlipItemTable($schema);
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     * @param QueryBag $queries
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updatePackingSlipItemTable(Schema $schema)
    {
        $table = $schema->getTable(self::MARELLO_PACKING_SLIP_ITEM_TABLE);
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'productUnit',
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
