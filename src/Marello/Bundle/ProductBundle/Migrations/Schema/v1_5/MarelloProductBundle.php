<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareTrait;

class MarelloProductBundle implements
    Migration,
    AttachmentExtensionAwareInterface
{
    use AttachmentExtensionAwareTrait;

    const MAX_PRODUCT_IMAGE_SIZE_IN_MB = 10;
    const MAX_PRODUCT_IMAGE_DIMENSIONS_IN_PIXELS = 250;
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Add Image attribute relation **/
        $this->addImageRelation($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function addImageRelation(Schema $schema)
    {
        $this->attachmentExtension->addImageRelation(
            $schema,
            'marello_product_product',
            'image',
            [
                'importexport' => ['excluded' => true]
            ],
            self::MAX_PRODUCT_IMAGE_SIZE_IN_MB,
            self::MAX_PRODUCT_IMAGE_DIMENSIONS_IN_PIXELS,
            self::MAX_PRODUCT_IMAGE_DIMENSIONS_IN_PIXELS
        );
    }
}
