<?php

namespace Marello\Bundle\WebhookBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\SchemaException;
use Marello\Bundle\WebhookBundle\Model\WebhookEventInterface;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloWebhookBundle implements Migration, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    public ExtendExtension $extendExtension;

    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addMarelloWebhookTable($schema);
        $this->updateOroIntegrationTransportTable($schema);
    }

    public function addMarelloWebhookTable(Schema $schema)
    {
        $table = $schema->createTable('marello_webhook');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['notnull' => false]);
        $table->addColumn('event', 'string', ['notnull' => false]);
        $table->addColumn('callback_url', 'string', ['notnull' => false]);
        $table->addColumn('secret', 'string', ['notnull' => false]);
        $table->addColumn('enabled', 'boolean', ['notnull' => false, 'default' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id']);
    }

    /**
     * @param Schema $schema
     * @throws SchemaException
     */
    public function updateOroIntegrationTransportTable(Schema $schema): void
    {
        $table = $schema->getTable('oro_integration_transport');

        $table->addColumn('webhook_signature_algo', 'string', ['notnull' => false, 'length' => 1024]);
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
