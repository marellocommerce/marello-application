<?php

namespace Marello\Bundle\CustomerBundle\Migrations\Schema\v1_4_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

use Marello\Bundle\CustomerBundle\Migrations\Schema\MarelloCustomerBundleInstaller;

class MarelloCustomerBundle implements Migration
{
    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(MarelloCustomerBundleInstaller::MARELLO_CUSTOMER_TABLE);
        // drop unique indexes for customer primary and shipping address
        if ($table->hasIndex('UNIQ_75C456C9F5B7AF75')) {
            $table->dropIndex('UNIQ_75C456C9F5B7AF75');
        }

        if ($table->hasIndex('UNIQ_75C456C94D4CFF2B')) {
            $table->dropIndex('UNIQ_75C456C94D4CFF2B');
        }

        if ($table->hasForeignKey('FK_AD0CE5A24D4CFF2B')) {
            $table->removeForeignKey('FK_AD0CE5A24D4CFF2B');
            $table->addForeignKeyConstraint(
                $schema->getTable('marello_address'),
                ['shipping_address_id'],
                ['id'],
                ['onDelete' => null, 'onUpdate' => null]
            );
        }
        if ($table->hasForeignKey('FK_AD0CE5A2CB134313')) {
            $table->removeForeignKey('FK_AD0CE5A2CB134313');
            $table->addForeignKeyConstraint(
                $schema->getTable('marello_address'),
                ['primary_address_id'],
                ['id'],
                ['onDelete' => null, 'onUpdate' => null]
            );
        }
    }
}
