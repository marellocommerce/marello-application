<?php

namespace Marello\Bundle\PdfBundle\Provider\Render;

use Marello\Bundle\PdfBundle\Provider\RenderParameterProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class ConfigValuesProvider implements RenderParameterProviderInterface
{
    const SCOPE_IDENTIFIER_KEY = 'config_scope';

    protected $config;

    protected $parameters;

    public function __construct(ConfigManager $config, array $parameters)
    {
        $this->config = $config;
        $this->parameters = $parameters;
    }

    public function supports($entity, array $options)
    {
        return true;
    }

    public function getParams($entity, array $options)
    {
        $params = [];
        foreach ($this->parameters as $key => $configKey) {
            $params[$key] = $this->getConfigValue($configKey, $entity, $options);
        }

        return $params;
    }

    protected function getConfigValue($configKey, $entity, array $options)
    {
        $scopeIdentifier = $options[self::SCOPE_IDENTIFIER_KEY] ?? null;

        return $this->config->get($configKey, false, false, $scopeIdentifier);
    }
}
