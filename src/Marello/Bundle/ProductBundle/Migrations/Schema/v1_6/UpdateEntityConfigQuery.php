<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_6;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

/**
 * Update entity config data using callback.
 */
class UpdateEntityConfigQuery extends ParametrizedMigrationQuery
{
    /**
     * @var callable
     */
    protected $updateCallback;

    /**
     * @var string
     */
    protected $className;

    /**
     * @param callable $updateCallback
     * @param string $className
     */
    public function __construct($updateCallback, $className)
    {
        $this->updateCallback = $updateCallback;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $data = $this->loadEntityConfigData($logger);
        $data = call_user_func($this->updateCallback, $data);

        $query  = 'UPDATE oro_entity_config SET data = :data WHERE class_name = :class_name';
        $params = ['data' => $data, 'class_name' => $this->className];
        $types  = ['data' => 'array', 'class_name' => 'string'];
        $this->logQuery($logger, $query, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($query, $params, $types);
        }
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return array
     */
    protected function loadEntityConfigData(LoggerInterface $logger)
    {
        $sql    = 'SELECT data FROM oro_entity_config WHERE class_name = :class_name';
        $params = ['class_name' => $this->className];
        $types  = ['class_name' => 'string'];
        $this->logQuery($logger, $sql, $params, $types);

        $result = [];

        $rows = $this->connection->fetchAll($sql, $params, $types);
        if (isset($rows[0])) {
            $result =  $this->connection->convertToPHPValue($rows[0]['data'], 'array');
        }

        return $result;
    }
}
