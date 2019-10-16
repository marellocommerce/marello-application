<?php

namespace Marello\Bundle\CustomerBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Types\Type;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\AttachmentBundle\Entity\Attachment;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\NoteBundle\Entity\Note;
use Psr\Log\LoggerInterface;

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
        $this->updateNoteEntityConfig($logger);
        $this->updateActivityListEntityConfig($logger);
        $this->updateAttachmentEntityConfig($logger);
        $this->updateCustomerEntityConfig($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateNoteEntityConfig(LoggerInterface $logger)
    {
        $entityName = Note::class;
        $oldRelation = 'manyToMany|Oro\Bundle\NoteBundle\Entity\Note|Marello\Bundle\OrderBundle\Entity\Customer|customer_c35c3934';

        $sql = 'SELECT id, data FROM oro_entity_config WHERE class_name = ? LIMIT 1';
        $parameters = [$entityName];
        $row = $this->connection->fetchAssoc($sql, $parameters);
        $this->logQuery($logger, $sql, $parameters);
        $id = $row['id'];
        $data = isset($row['data']) ? $this->connection->convertToPHPValue($row['data'], Type::TARRAY) : [];
        if (isset($data['extend']['relation'][$oldRelation])) {
            unset($data['extend']['schema']['relation']['customer_c35c3934']);
            unset($data['extend']['schema']['addremove']['customer_c35c3934']);
            unset($data['extend']['relation'][$oldRelation]);
            $data = $this->connection->convertToDatabaseValue($data, Type::TARRAY);

            $sql = 'UPDATE oro_entity_config SET data = ? WHERE id = ?';
            $parameters = [$data, $id];
            $statement = $this->connection->prepare($sql);
            $statement->execute($parameters);
            $this->logQuery($logger, $sql, $parameters);
        }
        $sql = 'DELETE FROM oro_entity_config_field WHERE entity_id = ? AND field_name = ?';
        $parameters = [$id, 'customer_c35c3934'];
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);
        $this->logQuery($logger, $sql, $parameters);
    }

    /**
     * @param LoggerInterface $logger
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateActivityListEntityConfig(LoggerInterface $logger)
    {
        $entityName = ActivityList::class;
        $oldRelation = 'manyToMany|Oro\Bundle\ActivityListBundle\Entity\ActivityList|Marello\Bundle\OrderBundle\Entity\Customer|customer_e64085f4';

        $sql = 'SELECT id, data FROM oro_entity_config WHERE class_name = ? LIMIT 1';
        $parameters = [$entityName];
        $row = $this->connection->fetchAssoc($sql, $parameters);
        $this->logQuery($logger, $sql, $parameters);
        $id = $row['id'];
        $data = isset($row['data']) ? $this->connection->convertToPHPValue($row['data'], Type::TARRAY) : [];
        if (isset($data['extend']['relation'][$oldRelation])) {
            unset($data['extend']['schema']['relation']['customer_e64085f4']);
            unset($data['extend']['schema']['addremove']['customer_e64085f4']);
            unset($data['extend']['relation'][$oldRelation]);

            $data = $this->connection->convertToDatabaseValue($data, Type::TARRAY);

            $sql = 'UPDATE oro_entity_config SET data = ? WHERE id = ?';
            $parameters = [$data, $id];
            $statement = $this->connection->prepare($sql);
            $statement->execute($parameters);
            $this->logQuery($logger, $sql, $parameters);
        }
        $sql = 'DELETE FROM oro_entity_config_field WHERE entity_id = ? AND field_name = ?';
        $parameters = [$id, 'customer_e64085f4'];
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);
        $this->logQuery($logger, $sql, $parameters);
    }

    /**
     * @param LoggerInterface $logger
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateAttachmentEntityConfig(LoggerInterface $logger)
    {
        $entityName = Attachment::class;
        $oldRelation = 'manyToOne|Oro\Bundle\AttachmentBundle\Entity\Attachment|Marello\Bundle\OrderBundle\Entity\Customer|customer_63c5df30';
        
        $sql = 'SELECT id, data FROM oro_entity_config WHERE class_name = ? LIMIT 1';
        $parameters = [$entityName];
        $row = $this->connection->fetchAssoc($sql, $parameters);
        $this->logQuery($logger, $sql, $parameters);
        $id = $row['id'];
        $data = isset($row['data']) ? $this->connection->convertToPHPValue($row['data'], Type::TARRAY) : [];
        if (isset($data['extend']['relation'][$oldRelation])) {
            unset($data['extend']['schema']['relation']['customer_63c5df30']);
            unset($data['extend']['schema']['addremove']['customer_63c5df30']);
            unset($data['extend']['relation'][$oldRelation]);

            $data = $this->connection->convertToDatabaseValue($data, Type::TARRAY);

            $sql = 'UPDATE oro_entity_config SET data = ? WHERE id = ?';
            $parameters = [$data, $id];
            $statement = $this->connection->prepare($sql);
            $statement->execute($parameters);
            $this->logQuery($logger, $sql, $parameters);
        }
        $sql = 'DELETE FROM oro_entity_config_field WHERE entity_id = ? AND field_name = ?';
        $parameters = [$id, 'customer_63c5df30'];
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);
        $this->logQuery($logger, $sql, $parameters);
    }
    
    /**
     * @param LoggerInterface $logger
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateCustomerEntityConfig(LoggerInterface $logger)
    {
        $sql = 'SELECT id FROM oro_entity_config WHERE class_name = ? LIMIT 1';
        $parameters = ['Marello\Bundle\OrderBundle\Entity\Customer'];
        $row = $this->connection->fetchAssoc($sql, $parameters);
        $this->logQuery($logger, $sql, $parameters);

        $entityId = $row['id'];
        $sql = 'DELETE FROM oro_entity_config_field WHERE entity_id = ?';
        $parameters = [$entityId];
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);
        $this->logQuery($logger, $sql, $parameters);

        $sql = 'DELETE FROM oro_entity_config WHERE id = ?';
        $parameters = [$entityId];
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);
        $this->logQuery($logger, $sql, $parameters);
    }
}
