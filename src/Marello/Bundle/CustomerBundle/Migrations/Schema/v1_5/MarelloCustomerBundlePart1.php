<?php

namespace Marello\Bundle\CustomerBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityBundle\ORM\DatabaseDriverInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\ConnectionAwareInterface;

use Marello\Bundle\CustomerBundle\Migrations\Schema\MarelloCustomerBundleInstaller;

class MarelloCustomerBundlePart1 implements Migration, OrderedMigrationInterface, ConnectionAwareInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(MarelloCustomerBundleInstaller::MARELLO_COMPANY_TABLE);
        $table->addColumn('tax_identification_number', 'string', ['notnull' => false, 'length' => 255]);
        if ($this->connection->getDriver()->getName() === DatabaseDriverInterface::DRIVER_POSTGRESQL) {
            $sql = <<<EOF
UPDATE marello_customer_company AS co SET tax_identification_number = cu.tax_identification_number FROM marello_customer_customer AS cu 
WHERE co.id = cu.company_id AND cu.tax_identification_number IS NOT NULL
EOF;
        } else {
            $sql = <<<EOF
UPDATE marello_customer_company co INNER JOIN marello_customer_customer cu ON co.id = cu.company_id SET co.tax_identification_number = cu.tax_identification_number
WHERE cu.tax_identification_number IS NOT NULL
EOF;
        }

        $queries->addPostQuery($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

}
