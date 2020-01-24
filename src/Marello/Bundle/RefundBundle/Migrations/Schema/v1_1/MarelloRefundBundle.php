<?php

namespace Marello\Bundle\RefundBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloRefundBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->dropWorkflowColumns($schema);
    }

    /**
     * Drop columns from marello_refund table
     *
     * @param Schema $schema
     */
    protected function dropWorkflowColumns(Schema $schema)
    {
        $table = $schema->getTable('marello_refund');

        if ($table->hasIndex('IDX_973FA8835E43682')) {
            $table->removeForeignKey('FK_973FA8831023C4EE');
            $table->dropIndex('IDX_973FA8835E43682');
        }
        $table->dropColumn('workflow_item_id');


        if ($table->hasIndex('IDX_973FA88364397A40')) {
            $table->removeForeignKey('FK_973FA88371FE882C');
            $table->dropIndex('IDX_973FA88364397A40');
        }
        $table->dropColumn('workflow_step_id');
    }
}
