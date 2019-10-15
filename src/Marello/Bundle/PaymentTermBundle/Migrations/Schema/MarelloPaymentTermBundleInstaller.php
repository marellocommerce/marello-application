<?php

namespace Marello\Bundle\PaymentTermBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @inheritDoc
 */
class MarelloPaymentTermBundleInstaller implements Installation
{
    /**
     * @inheritDoc
     */
    public function getMigrationVersion()
    {
        return 'v1_1';
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::createPaymentTermTable($schema);
        self::createPaymentTermLabelsTable($schema);
        self::createForeignKeys($schema);
        self::createMarelloPaymentTermTransportLabelTable($schema);
        self::addMarelloPaymentTermTransportLabelForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected static function createPaymentTermTable(Schema $schema)
    {
        $table = $schema->createTable('marello_payment_term');
        $table->addColumn('id', 'integer', ['notnull' => true, 'autoincrement' => true]);
        $table->addColumn('code', 'string', ['length' => 32, 'notnull' => true,]);
        $table->addColumn('term', 'integer', ['notnull' => true,]);

        $table->addUniqueIndex(['code']);

        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    protected static function createPaymentTermLabelsTable(Schema $schema)
    {
        $table = $schema->createTable('marello_payment_term_labels');
        $table->addColumn('paymentterm_id', 'integer', ['notnull' => true,]);
        $table->addColumn('localized_value_id', 'integer', ['notnull' => true]);

        $table->addUniqueIndex(['localized_value_id']);

        $table->setPrimaryKey(['paymentterm_id', 'localized_value_id']);
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
     */
    protected static function createForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_payment_term_labels');
        $table->addForeignKeyConstraint(
            'marello_payment_term',
            ['paymentterm_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            'oro_fallback_localization_val',
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
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
