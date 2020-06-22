<?php

namespace Marello\Bundle\RuleBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloRuleBundleAddIsSystemColumn implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 10;
    }


    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloRuleTable($schema, $queries);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function updateMarelloRuleTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_rule');

        $table->addColumn('is_system', 'boolean', ['default' => false]);
        $query = "
            UPDATE marello_rule
                SET
                    is_system = system";
        $queries->addQuery($query);
    }
}
