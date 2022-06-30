<?php

namespace Marello\Bundle\InvoiceBundle\Migrations\Schema\v3_0;

use Psr\Log\LoggerInterface;

use Doctrine\DBAL\Types\Types;

use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

use Marello\Bundle\InvoiceBundle\Entity\Creditmemo;

class UpdateEntityConfigExtendClassQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Update entity extend class configuration on given entity';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->updateCreditmemoEntityConfig($logger);
        $this->updateCreditmemoItemEntityConfig($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateCreditmemoEntityConfig(LoggerInterface $logger)
    {
        $entityName = Creditmemo::class;
        $oldRelation = 'Extend\Entity\EX_MarelloInvoiceBundle_Invoice';
        $newRelation = 'Extend\Entity\EX_MarelloInvoiceBundle_Creditmemo';

        $sql = 'SELECT id, data FROM oro_entity_config WHERE class_name = ? LIMIT 1';
        $parameters = [$entityName];
        $row = $this->connection->fetchAssoc($sql, $parameters);
        $this->logQuery($logger, $sql, $parameters);
        $id = $row['id'];
        $data = isset($row['data']) ? $this->connection->convertToPHPValue($row['data'], Types::ARRAY) : [];
        if (isset($data['extend']['extend_class'][$oldRelation])) {
            $data['extend']['extend_class'][] = $newRelation;
            $data = $this->connection->convertToDatabaseValue($data, Types::ARRAY);

            $sql = 'UPDATE oro_entity_config SET data = ? WHERE id = ?';
            $parameters = [$data, $id];
            $statement = $this->connection->prepare($sql);
            $statement->execute($parameters);
            $this->logQuery($logger, $sql, $parameters);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateCreditmemoItemEntityConfig(LoggerInterface $logger)
    {
        $entityName = Creditmemo::class;
        $oldRelation = 'Extend\Entity\EX_MarelloInvoiceBundle_InvoiceItem';
        $newRelation = 'Extend\Entity\EX_MarelloInvoiceBundle_CreditmemoItem';

        $sql = 'SELECT id, data FROM oro_entity_config WHERE class_name = ? LIMIT 1';
        $parameters = [$entityName];
        $row = $this->connection->fetchAssoc($sql, $parameters);
        $this->logQuery($logger, $sql, $parameters);
        $id = $row['id'];
        $data = isset($row['data']) ? $this->connection->convertToPHPValue($row['data'], Types::ARRAY) : [];
        if (isset($data['extend']['extend_class'][$oldRelation])) {
            $data['extend']['extend_class'][] = $newRelation;
            $data = $this->connection->convertToDatabaseValue($data, Types::ARRAY);

            $sql = 'UPDATE oro_entity_config SET data = ? WHERE id = ?';
            $parameters = [$data, $id];
            $statement = $this->connection->prepare($sql);
            $statement->execute($parameters);
            $this->logQuery($logger, $sql, $parameters);
        }
    }
}
