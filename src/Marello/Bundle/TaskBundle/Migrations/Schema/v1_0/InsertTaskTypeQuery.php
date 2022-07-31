<?php

namespace Marello\Bundle\TaskBundle\Migrations\Schema\v1_0;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class InsertTaskTypeQuery extends ParametrizedMigrationQuery
{
    public function __construct(
        protected ExtendExtension $extendExtension
    ) {}

    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info(
            'Insert default task types.'
        );
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $tableName = $this->extendExtension->getNameGenerator()->generateEnumTableName('task_type');

        $sql = 'INSERT INTO %s (id, name, priority, is_default) VALUES (:id, :name, :priority, :is_default)';
        $sql = sprintf($sql, $tableName);

        $statuses = [
            [
                ':id' => 'general',
                ':name' => 'General',
                ':priority' => 1,
                ':is_default' => true,
            ],
            [
                ':id' => 'allocation',
                ':name' => 'Allocation',
                ':priority' => 2,
                ':is_default' => false,
            ],
        ];

        $types = [
            'id' => 'string',
            'name' => 'string',
            'priority' => 'integer',
            'is_default' => 'boolean'
        ];

        foreach ($statuses as $status) {
            $this->logQuery($logger, $sql, $status, $types);
            if (!$dryRun) {
                $this->connection->executeStatement($sql, $status, $types);
            }
        }
    }
}
