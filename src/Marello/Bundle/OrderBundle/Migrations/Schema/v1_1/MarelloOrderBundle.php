<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloOrderBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->dropWorkflowColumns($schema);
    }

    /**
     * Drop columns from marello_order_order table
     *
     * @param Schema $schema
     */
    protected function dropWorkflowColumns(Schema $schema)
    {
        $table = $schema->getTable('marello_order_order');

        if ($table->hasIndex('UNIQ_A619DD641023C4EE')) {
            $table->removeForeignKey('FK_A619DD641023C4EE');
            $table->dropIndex('UNIQ_A619DD641023C4EE');
        }
        $table->dropColumn('workflow_item_id');


        if ($table->hasIndex('IDX_A619DD6471FE882C')) {
            $table->removeForeignKey('FK_A619DD6471FE882C');
            $table->dropIndex('IDX_A619DD6471FE882C');
        }
        $table->dropColumn('workflow_step_id');
    }
}
