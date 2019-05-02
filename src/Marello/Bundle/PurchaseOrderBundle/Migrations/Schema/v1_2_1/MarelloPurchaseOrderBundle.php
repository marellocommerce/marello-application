<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_2_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloPurchaseOrderBundle implements
    Migration,
    ActivityExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updatePurchaseOrderTable($schema, $queries);
    }

    /**
     * Creates supplier column and sets the value of the current purchase orders
     *
     * @param Schema $schema
     */
    protected function updatePurchaseOrderTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_purchase_order');
        $table->addColumn('supplier_id', 'integer', ['notnull' => false]);

        $query = "
            UPDATE marello_purchase_order po
                SET
                    supplier_id = (
                    	SELECT id FROM marello_supplier_supplier sup WHERE sup.name = (
	                      SELECT
	                        supplier
	                      FROM
	                        marello_purchase_order_item poi
	                      WHERE 
	                        poi.order_id = po.id
	                        GROUP BY supplier
	                        LIMIT 1
                ))
        ";
        $queries->addQuery($query);

        $this->activityExtension->addActivityAssociation($schema, 'marello_notification', $table->getName());
    }

    /**
     * Sets the ActivityExtension
     *
     * @param ActivityExtension $activityExtension
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }
}
