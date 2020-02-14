<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;

use Marello\Bundle\ProductBundle\Entity\Product;

class UpdateAttachmentFieldConfigForProductImage implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(Product::class, 'image', 'attribute', 'is_attribute', true)
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(Product::class, 'image', 'extend', 'owner', ExtendScope::OWNER_CUSTOM)
        );
    }
}
