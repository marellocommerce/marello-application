<?php

namespace Marello\Bundle\PaymentBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class MarelloPaymentBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface,
    ExtendExtensionAwareInterface
{
    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v2_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloPaymentPaymentTable($schema);
        $this->createMarelloPaymentMethodConfigTable($schema);
        $this->createMarelloPaymentMethodsConfigsRuleTable($schema);
        $this->createMarelloPaymentMethodsConfigsRuleDestinationTable($schema);
        $this->createMarelloPaymentMethodsConfigsRuleDestinationPostalCodeTable($schema);

        $this->addMarelloPaymentPaymentForeignKeys($schema);
        $this->addMarelloPaymentMethodConfigForeignKeys($schema);
        $this->addMarelloPaymentMethodConfigForeignKeys($schema);
        $this->addMarelloPaymentMethodsConfigsRuleForeignKeys($schema);
        $this->addMarelloPaymentMethodsConfigsRuleDestinationForeignKeys($schema);
        $this->addMarelloPaymentMethodsConfigsRuleDestinationPostalCodeForeignKeys($schema);
    }

    /**
     * Create marello_payment_payment table
     *
     * @param Schema $schema
     */
    protected function createMarelloPaymentPaymentTable(Schema $schema)
    {
        $table = $schema->createTable('marello_payment_payment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('payment_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn(
            'payment_method_options',
            'json_array',
            [
                'notnull' => false, 'comment' => '(DC2Type:json_array)'
            ]
        );
        $table->addColumn('payment_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('payment_details', 'text', ['notnull' => false]);
        $table->addColumn('total_paid', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('payment_date', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'status',
            'marello_paymnt_status',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id']);
    }

    /**
     * Create marello_payment_method_config table
     *
     * @param Schema $schema
     */
    protected function createMarelloPaymentMethodConfigTable(Schema $schema)
    {
        $table = $schema->createTable('marello_payment_method_config');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('configs_rule_id', 'integer', []);
        $table->addColumn('method', 'string', ['length' => 255]);
        $table->addColumn('options', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create marello_payment_mtds_cfgs_rl table
     *
     * @param Schema $schema
     */
    protected function createMarelloPaymentMethodsConfigsRuleTable(Schema $schema)
    {
        $table = $schema->createTable('marello_payment_mtds_cfgs_rl');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('rule_id', 'integer', []);
        $table->addColumn('currency', 'string', ['notnull' => true, 'length' => 3]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);

        $this->activityExtension->addActivityAssociation(
            $schema,
            'oro_note',
            'marello_payment_mtds_cfgs_rl'
        );
    }

    /**
     * Create marello_payment_mtds_cfgs_rl_d table
     *
     * @param Schema $schema
     */
    protected function createMarelloPaymentMethodsConfigsRuleDestinationTable(Schema $schema)
    {
        $table = $schema->createTable('marello_payment_mtds_cfgs_rl_d');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('configs_rule_id', 'integer', []);
        $table->addColumn('country_code', 'string', ['length' => 2]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create marello_pmnt_mtdscfgsrl_dst_pc table
     *
     * @param Schema $schema
     */
    protected function createMarelloPaymentMethodsConfigsRuleDestinationPostalCodeTable(Schema $schema)
    {
        $table = $schema->createTable('marello_pmnt_mtdscfgsrl_dst_pc');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('destination_id', 'integer', []);
        $table->addColumn('name', 'text', []);
        $table->setPrimaryKey(['id']);
    }
    
    /**
     * Add marello_payment_payment foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloPaymentPaymentForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_payment_payment');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
    
    /**
     * Add marello_payment_method_config foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloPaymentMethodConfigForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_payment_method_config');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_payment_mtds_cfgs_rl'),
            ['configs_rule_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_payment_mtds_cfgs_rl foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloPaymentMethodsConfigsRuleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_payment_mtds_cfgs_rl');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_rule'),
            ['rule_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_payment_mtds_cfgs_rl_d foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloPaymentMethodsConfigsRuleDestinationForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_payment_mtds_cfgs_rl_d');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_payment_mtds_cfgs_rl'),
            ['configs_rule_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add marello_pmnt_mtdscfgsrl_dst_pc foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloPaymentMethodsConfigsRuleDestinationPostalCodeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_pmnt_mtdscfgsrl_dst_pc');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_payment_mtds_cfgs_rl_d'),
            ['destination_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Sets the ActivityExtension
     *
     * @param ActivityExtension $activityExtension
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * Sets the ExtendExtension
     *
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
