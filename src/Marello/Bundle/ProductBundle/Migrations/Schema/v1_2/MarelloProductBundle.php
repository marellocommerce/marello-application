<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class MarelloProductBundle implements Migration, ExtendExtensionAwareInterface
{
    const PRODUCT_TABLE_NAME = 'marello_product_product';

    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->addMarelloProductProductTable($schema);
    }

    /**
     * Create marello_product_product table
     *
     * @param Schema $schema
     */
    protected function addMarelloProductProductTable(Schema $schema)
    {
        $this->extendExtension->addEnumField(
            $schema,
            $schema->getTable(self::PRODUCT_TABLE_NAME),
            'replenishment',
            'marello_product_reple',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
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
