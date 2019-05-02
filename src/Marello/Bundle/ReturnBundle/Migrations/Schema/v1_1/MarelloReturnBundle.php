<?php

namespace Marello\Bundle\ReturnBundle\Migrations\Schema\v1_1;

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
        $this->dropWorkflowColumns($schema);
    }

    /**
     * Drop columns from marello_return_return table
     *
     * @param Schema $schema
     */
    protected function dropWorkflowColumns(Schema $schema)
    {
        $table = $schema->getTable('marello_return_return');

        if ($table->hasIndex('uniq_3c549d8d1023c4ee')) {
            $table->removeForeignKey('FK_3C549D8D1023C4EE');
            $table->dropIndex('uniq_3c549d8d1023c4ee');
        }
        $table->dropColumn('workflow_item_id');


        if ($table->hasIndex('idx_3c549d8d71fe882c')) {
            $table->removeForeignKey('FK_3C549D8D71FE882C');
            $table->dropIndex('idx_3c549d8d71fe882c');
        }
        $table->dropColumn('workflow_step_id');
    }
}
