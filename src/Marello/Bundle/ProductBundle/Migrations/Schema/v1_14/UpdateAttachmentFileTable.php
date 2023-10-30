<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

class UpdateAttachmentFileTable implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Add additional media url */
        $this->addAdditionalMediaUrl($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function addAdditionalMediaUrl(Schema $schema)
    {
        $table = $schema->getTable('oro_attachment_file');
        if (!$table->hasColumn('media_url')) {
            $table->addColumn('media_url', 'string', [
                'oro_options' => [
                    'extend' => [
                        'is_extend' => true,
                        'owner' => ExtendScope::OWNER_CUSTOM,
                        'nullable' => true,
                        'on_delete' => 'SET NULL'
                    ],
                    'entity' => [
                        'label' => 'marello.attachment.file.media_url.label',
                        'description' => 'marello.attachment.file.media_url.description'
                    ]
                ],
                [
                    'length' => 255,
                    'notnull' => false
                ]
            ]);
        }
    }
}
