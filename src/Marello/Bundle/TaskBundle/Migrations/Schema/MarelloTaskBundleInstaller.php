<?php

namespace Marello\Bundle\TaskBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Marello\Bundle\TaskBundle\Migrations\Schema\v1_0\MarelloTaskBundle;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloTaskBundleInstaller implements
    Installation,
    ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        MarelloTaskBundle::addTaskTypeField($schema, $this->extendExtension);
        MarelloTaskBundle::addTaskTypeValues($queries, $this->extendExtension);
        MarelloTaskBundle::addAssignToRelations($schema, $this->extendExtension);
    }
}
