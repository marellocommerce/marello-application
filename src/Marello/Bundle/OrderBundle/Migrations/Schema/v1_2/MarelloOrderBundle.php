<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v1_2;

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
    const MARELLO_ORDER_TABLE = 'marello_order_order_item';
    const MARELLO_TAX_CODE_TABLE = 'marello_tax_tax_code';
    
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(self::MARELLO_ORDER_TABLE);
        $table->addColumn('tax_code_id', 'integer', ['notnull' => false]);

        $table->addForeignKeyConstraint(
            $schema->getTable(self::MARELLO_TAX_CODE_TABLE),
            ['tax_code_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
