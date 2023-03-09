<?php

namespace Marello\Bundle\WebhookBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Marello\Bundle\WebhookBundle\Migrations\Schema\v1_0\MarelloWebhookBundle;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloWebhookBundleInstaller implements Installation, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    public $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $marelloWebhookBundle = new MarelloWebhookBundle();
        $marelloWebhookBundle->setExtendExtension($this->extendExtension);
        $marelloWebhookBundle->up($schema, $queries);
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
