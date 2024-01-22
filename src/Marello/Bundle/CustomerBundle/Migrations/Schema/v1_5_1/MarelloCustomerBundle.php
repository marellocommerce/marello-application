<?php

namespace Marello\Bundle\CustomerBundle\Migrations\Schema\v1_5_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

use Marello\Bundle\CustomerBundle\Migrations\Schema\MarelloCustomerBundleInstaller;

class MarelloCustomerBundle implements Migration
{
    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(MarelloCustomerBundleInstaller::MARELLO_CUSTOMER_TABLE);
        // drop unique indexes for customer primary and shipping address
        if (!$table->hasIndex('marello_customer_emailidx')) {
            $table->addUniqueIndex(['email', 'organization_id'], 'marello_customer_emailorgidx');
        }
    }
}
