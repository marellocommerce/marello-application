<?php

namespace Marello\Bundle\TaskBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloTaskBundle implements
    Migration,
    ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        self::addTaskTypeField($schema, $this->extendExtension);
        self::addTaskTypeValues($queries, $this->extendExtension);
        self::addRelationToGroup($schema, $this->extendExtension);
    }

    public static function addTaskTypeField(Schema $schema, ExtendExtension $extendExtension)
    {
        $enumTable = $extendExtension->addEnumField(
            $schema,
            'orocrm_task',
            'type',
            'task_type'
        );

        $options = new OroOptions();
        $options->set('enum', 'immutable_codes', ['general', 'allocation']);

        $enumTable->addOption(OroOptions::KEY, $options);
    }

    public static function addTaskTypeValues(QueryBag $queries, ExtendExtension $extendExtension)
    {
        $queries->addPostQuery(new InsertTaskTypeQuery($extendExtension));
    }

    public static function addAssignToRelations(Schema $schema, ExtendExtension $extendExtension)
    {
        $extendExtension->addManyToOneRelation(
            $schema,
            'orocrm_task',
            'assignedToUser',
            'oro_user',
            'username',
            [
                'extend' => [
                    'owner' => ExtendScope::OWNER_CUSTOM,
                ],
                'datagrid' => [
                    'is_visible' => DatagridScope::IS_VISIBLE_TRUE,
                    'show_filter' => true,
                ],
                'view' => [
                    'is_displayable' => true,
                ]
            ]
        );
        $extendExtension->addManyToOneRelation(
            $schema,
            'orocrm_task',
            'assignedToGroup',
            'oro_access_group',
            'name',
            [
                'extend' => [
                    'owner' => ExtendScope::OWNER_CUSTOM,
                ],
                'datagrid' => [
                    'is_visible' => DatagridScope::IS_VISIBLE_TRUE,
                    'show_filter' => true,
                ],
                'view' => [
                    'is_displayable' => true,
                ]
            ]
        );
    }
}
