<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v1_8_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloOrderBundle implements Migration, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $tableName = $this->extendExtension->getNameGenerator()->generateEnumTableName('marello_item_status');
        // enum table is already available and created...
        if ($schema->hasTable($tableName)) {
            return;
        }

        $table = $schema->getTable('marello_order_order_item');
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'status',
            'marello_item_status',
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
