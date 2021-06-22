<?php

namespace Marello\Bundle\ReturnBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloReturnBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_return_return');

        $table->addColumn('received_at', 'datetime', []);
        $table->addColumn('track_trace_code', 'string', ['length' => 255]);
    }
}
