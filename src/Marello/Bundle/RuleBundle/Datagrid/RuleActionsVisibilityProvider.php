<?php

namespace Marello\Bundle\RuleBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Marello\Bundle\RuleBundle\Entity\RuleInterface;

class RuleActionsVisibilityProvider
{
    const ENABLE_ACTION = 'enable';
    const DISABLE_ACTION = 'disable';
    const UPDATE_ACTION = 'update';
    const DELETE_ACTION = 'delete';

    /**
     * @param ResultRecordInterface $record
     * @param array $actions
     * @return array
     */
    public function getActionsVisibility(ResultRecordInterface $record, array $actions)
    {
        $actions = array_keys($actions);
        $visibility = [];
        foreach ($actions as $action) {
            $visibility[$action] = true;
        }

        $rule = $record->getValue('rule');

        if ($rule instanceof RuleInterface) {
            if ($rule->isSystem()) {
                $visibility[self::ENABLE_ACTION] = false;
                $visibility[self::DISABLE_ACTION] = false;
                $visibility[self::UPDATE_ACTION] = false;
                $visibility[self::DELETE_ACTION] = false;
            } else {
                if (array_key_exists(self::ENABLE_ACTION, $visibility)) {
                    $visibility[self::ENABLE_ACTION] = !$rule->isEnabled();
                }

                if (array_key_exists(self::DISABLE_ACTION, $visibility)) {
                    $visibility[self::DISABLE_ACTION] = $rule->isEnabled();
                }
            }
        }

        return $visibility;
    }
}
