<?php

namespace Marello\Bundle\RuleBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloRuleBundle implements Migration
{
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

        $table->addColumn('expression', 'text', ['notnull' => false]);
    }
}
