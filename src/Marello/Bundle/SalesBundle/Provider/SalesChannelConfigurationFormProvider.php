<?php

namespace Marello\Bundle\SalesBundle\Provider;

use Oro\Bundle\ConfigBundle\Provider\AbstractProvider;

class SalesChannelConfigurationFormProvider extends AbstractProvider
{
    const CONFIG_KEY = 'saleschannel_configuration';

    private $parentCheckboxLabel = 'marello.sales.config.use_default.label';

    protected function getTreeName(): string
    {
        return self::CONFIG_KEY;
    }

    public function setParentCheckboxLabel($label)
    {
        $this->parentCheckboxLabel = $label;
    }

    protected function getParentCheckboxLabel(): string
    {
        return $this->parentCheckboxLabel;
    }
}
