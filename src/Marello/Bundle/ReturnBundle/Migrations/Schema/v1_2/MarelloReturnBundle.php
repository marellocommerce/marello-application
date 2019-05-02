<?php

namespace Marello\Bundle\ReturnBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloReturnBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->modifyMarelloReturnReturnIndexes($schema);
    }

    /**
     * Drop columns from marello_return_return table
     *
     * @param Schema $schema
     */
    protected function modifyMarelloReturnReturnIndexes(Schema $schema)
    {
        $table = $schema->getTable('marello_return_return');
        $table->addUniqueIndex(['shipment_id'], 'UNIQ_3C549D8D7BE036FC');

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_shipment'),
            ['shipment_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null],
            'FK_3C549D8D7BE036FC'
        );
    }
}
