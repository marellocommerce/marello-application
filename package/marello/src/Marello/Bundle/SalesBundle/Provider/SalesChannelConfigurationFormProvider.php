<?php

namespace Marello\Bundle\SalesBundle\Provider;

use Oro\Bundle\ConfigBundle\Provider\AbstractProvider;

class SalesChannelConfigurationFormProvider extends AbstractProvider
{
    const CONFIG_KEY = 'saleschannel_configuration';

    private $parentCheckboxLabel = 'marello.sales.config.use_default.label';

    public function getTree()
    {
        return $this->getTreeData(self::CONFIG_KEY, self::CORRECT_FIELDS_NESTING_LEVEL);
    }

    public function getJsTree()
    {
        return $this->getJsTreeData(self::CONFIG_KEY, self::CORRECT_MENU_NESTING_LEVEL);
    }

    public function setParentCheckboxLabel($label)
    {
        $this->parentCheckboxLabel = $label;
    }

    protected function getParentCheckboxLabel()
    {
        return $this->parentCheckboxLabel;
    }
}
