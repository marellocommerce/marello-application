<?php

namespace Marello\Bundle\SupplierBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloSupplierBundle implements Migration, RenameExtensionAwareInterface
{
    /**
     * @var RenameExtension
     */
    private $renameExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->renameMarelloSupplierProductSupplierRelationTable($schema, $queries);
        $this->modifyMarelloSupplierSupplierTableIndexes($schema);
    }

    /**
     * Rename marello_supplier_supplier table
     *
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function renameMarelloSupplierProductSupplierRelationTable(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('marello_supplier_prod_supp_rel')) {
            $fromTable = 'marello_supplier_prod_supp_rel';
            $toTable = 'marello_product_prod_supp_rel';

            $this->renameExtension->renameTable($schema, $queries, $fromTable, $toTable);
        }
    }
    /**
     * Create marello_supplier_supplier table
     *
     * @param Schema $schema
     */
    protected function modifyMarelloSupplierSupplierTableIndexes(Schema $schema)
    {
        $table = $schema->getTable('marello_supplier_supplier');
        if ($table->hasIndex('IDX_16532C7BF5B7AF75')) {
            $table->dropIndex('IDX_16532C7BF5B7AF75');
        }
        $table->addUniqueIndex(['address_id'], 'UNIQ_16532C7BF5B7AF75', []);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension = $renameExtension;
    }
}
