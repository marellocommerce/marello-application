<?php

namespace Marello\Bundle\PaymentTermBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @inheritDoc
 */
class MarelloPaymentTermBundleInstaller implements Migration
{
    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::createMarelloPaymentTermTransportLabelTable($schema);
        self::addMarelloPaymentTermTransportLabelForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected static function createMarelloPaymentTermTransportLabelTable(Schema $schema)
    {
        $table = $schema->createTable('marello_payment_term_trans_lbl');

        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);

        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addIndex(['transport_id'], 'marello_payment_term_trans_label_transport_id', []);
        $table->addUniqueIndex(['localized_value_id'], 'marello_payment_term_trans_label_localized_value_id', []);
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    protected static function addMarelloPaymentTermTransportLabelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_payment_term_trans_lbl');

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

}
