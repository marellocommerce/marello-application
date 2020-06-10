<?php

namespace Marello\Bundle\CustomerBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

use Marello\Bundle\CustomerBundle\Migrations\Schema\MarelloCustomerBundleInstaller;

class MarelloCustomerBundle implements Migration, ActivityExtensionAwareInterface
{
    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(MarelloCustomerBundleInstaller::MARELLO_COMPANY_TABLE);
        $table->addColumn('code', 'string', ['length' => 255, 'notnull' => false]);
    }

    /**
     * Sets the ActivityExtension
     *
     * @param ActivityExtension $activityExtension
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }
}
