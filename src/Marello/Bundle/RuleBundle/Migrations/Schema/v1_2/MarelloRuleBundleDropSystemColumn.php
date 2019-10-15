<?php

namespace Marello\Bundle\RuleBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloRuleBundleDropSystemColumn implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 20;
    }


    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloRuleTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateMarelloRuleTable(Schema $schema)
    {
        $table = $schema->getTable('marello_rule');

        $table->dropColumn('system');

    }
}
