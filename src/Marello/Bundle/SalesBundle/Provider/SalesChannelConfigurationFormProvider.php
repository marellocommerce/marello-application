<?php

namespace Marello\Bundle\SalesBundle\Provider;

use Oro\Bundle\ConfigBundle\Provider\AbstractProvider;

class SalesChannelConfigurationFormProvider extends AbstractProvider
{
    const CONFIG_KEY = 'sales_channel_configuration';

    public function getTree()
    {
        return $this->getTreeData(self::CONFIG_KEY, self::CORRECT_FIELDS_NESTING_LEVEL);
    }

    public function getJsTree()
    {
        return $this->getJsTreeData(self::CONFIG_KEY, self::CORRECT_MENU_NESTING_LEVEL);
    }

    protected function getParentCheckboxLabel()
    {
        return 'oro.config.system_configuration.use_default';
    }
}
