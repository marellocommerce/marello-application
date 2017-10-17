<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareTrait;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;

class MarelloProductBundle implements
    Migration,
    AttachmentExtensionAwareInterface
{
    use AttachmentExtensionAwareTrait;

    const PRODUCT_TABLE = 'marello_product_product';
    const MAX_PRODUCT_IMAGE_SIZE_IN_MB = 1;
    const MAX_PRODUCT_IMAGE_DIMENSIONS_IN_PIXELS = 250;
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Add Image attribute relation **/
        $this->addImageRelation($schema);

        /** Add Manufacturing code attribute */
        $this->addManufacturingCode($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function addImageRelation(Schema $schema)
    {
        $this->attachmentExtension->addImageRelation(
            $schema,
            self::PRODUCT_TABLE,
            'image',
            [
                'importexport' => ['excluded' => true]
            ],
            self::MAX_PRODUCT_IMAGE_SIZE_IN_MB,
            self::MAX_PRODUCT_IMAGE_DIMENSIONS_IN_PIXELS,
            self::MAX_PRODUCT_IMAGE_DIMENSIONS_IN_PIXELS
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addManufacturingCode(Schema $schema)
    {
        $table = $schema->getTable(self::PRODUCT_TABLE);
        $table->addColumn('manufacturing_code', 'string', ['length' => 255, 'notnull' => false]);
    }
}
