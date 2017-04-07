<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloPurchaseOrderBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->dropWorkflowColumns($schema);
    }

    /**
     * Drop columns from marello_purchase_order table
     *
     * @param Schema $schema
     */
    protected function dropWorkflowColumns(Schema $schema)
    {
        $table = $schema->getTable('marello_purchase_order');

        if ($table->hasIndex('UNIQ_34E72AC31023C4EE')) {
            $table->removeForeignKey('FK_34E72AC31023C4EE');
            $table->dropIndex('UNIQ_34E72AC31023C4EE');
        }
        $table->dropColumn('workflow_item_id');


        if ($table->hasIndex('IDX_34E72AC371FE882C')) {
            $table->removeForeignKey('FK_34E72AC371FE882C');
            $table->dropIndex('IDX_34E72AC371FE882C');
        }
        $table->dropColumn('workflow_step_id');
    }
}
