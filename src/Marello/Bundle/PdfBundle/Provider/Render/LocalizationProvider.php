<?php

namespace Marello\Bundle\PdfBundle\Provider\Render;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\PdfBundle\Provider\RenderParameterProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LocaleBundle\Entity\Localization;

class LocalizationProvider implements RenderParameterProviderInterface
{
    const SCOPE_IDENTIFIER_KEY = 'config_scope';

    /**
     * @var ConfigManager
     */
    protected $config;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var string
     */
    protected $localizationParameterName;

    public function __construct(ConfigManager $config, Registry $doctrine, $localizationParameterName)
    {
        $this->config = $config;
        $this->doctrine = $doctrine;
        $this->localizationParameterName = $localizationParameterName;
    }

    public function supports($entity, array $options)
    {
        return true;
    }

    public function getParams($entity, array $options)
    {
        $params = [];
        $localizationId = (int)$this->getConfigValue($this->localizationParameterName, $entity, $options);
        $localization = $this->doctrine
            ->getManagerForClass(Localization::class)
            ->find(Localization::class, $localizationId);
        if ($localization) {
            $params['localization'] = $localization;
            $params['language'] = $localization->getLanguageCode();
            $params['locale'] = $localization->getLanguageCode();

        }

        return $params;
    }

    protected function getConfigValue($configKey, $entity, array $options)
    {
        $scopeIdentifier = $options[self::SCOPE_IDENTIFIER_KEY] ?? null;

        return $this->config->get($configKey, false, false, $scopeIdentifier);
    }
}
