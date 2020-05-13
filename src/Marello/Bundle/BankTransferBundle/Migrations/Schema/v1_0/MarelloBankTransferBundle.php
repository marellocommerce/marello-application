<?php

namespace Marello\Bundle\BankTransferBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloBankTransferBundle implements Migration
{

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloBankTransferTransportLabelTable($schema);
        $this->addMarelloBankTransferTransportLabelForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    private function createMarelloBankTransferTransportLabelTable(Schema $schema)
    {
        $table = $schema->createTable('marello_bank_transfer_tr_lbl');

        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);

        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addIndex(['transport_id'], 'marello_bank_transfer_trans_label_transport_id', []);
        $table->addUniqueIndex(['localized_value_id'], 'marello_bank_transfer_trans_label_localized_value_id', []);
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    private function addMarelloBankTransferTransportLabelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_bank_transfer_tr_lbl');

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
