<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v1_13;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;

use Marello\Bundle\OrderBundle\Model\OrderStatusesInterface;

class MarelloOrderBundle implements Migration, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOrderTable($schema);
    }

    private function updateOrderTable(Schema $schema)
    {
        $tableName = $this->extendExtension
            ->getNameGenerator()
            ->generateEnumTableName(OrderStatusesInterface::ORDER_STATUS_ENUM_CLASS);
        // enum table is already available and created...
        if ($schema->hasTable($tableName)) {
            return;
        }

        $table = $schema->getTable('marello_order_order');
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'orderStatus',
            OrderStatusesInterface::ORDER_STATUS_ENUM_CLASS,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
    }

    /**
     * Sets the ExtendExtension
     *
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
