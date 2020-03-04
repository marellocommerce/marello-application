<?php

namespace Marello\Bundle\CustomerBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MigrateExtendedData implements
    Migration,
    OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                'INSERT INTO oro_rel_6f8f552a784fec5f5daa7e SELECT * FROM oro_rel_6f8f552a784fec5f18d607'
            )
        );
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                'INSERT INTO oro_rel_c3990ba6784fec5f7f9667 SELECT * FROM oro_rel_c3990ba6784fec5f21b990'
            )
        );
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                'UPDATE oro_attachment SET customer_d5a7d2a4_id = customer_63c5df30_id'
            )
        );
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                'DELETE FROM marello_order_customer'
            )
        );
        $attachmentTable = $schema->getTable('oro_attachment');
        $attachmentTable->dropColumn('customer_63c5df30_id');
        $attachmentTable->removeForeignKey('FK_FA0FE081ADEA609C');
        $schema->dropTable('oro_rel_6f8f552a784fec5f18d607');
        $schema->dropTable('oro_rel_c3990ba6784fec5f21b990');
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 30;
    }
}
