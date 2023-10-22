<?php

namespace Marello\Bundle\PdfBundle\Provider\Render;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;
use Marello\Bundle\LocaleBundle\Model\LocalizationAwareInterface;
use Marello\Bundle\PdfBundle\Provider\RenderParameterProviderInterface;
use Marello\Bundle\LocaleBundle\Provider\EntityLocalizationProviderInterface;

class LocalizationProvider implements RenderParameterProviderInterface
{
    const SCOPE_IDENTIFIER_KEY = 'config_scope';

    /** @var EntityLocalizationProviderInterface $chainLocalizationProvider */
    protected $chainLocalizationProvider;

    /**
     * LocalizationProvider constructor.
     * @param ConfigManager $config
     * @param Registry $doctrine
     * @param $localizationParameterName
     */
    public function __construct(
        protected ConfigManager $config,
        protected ManagerRegistry $doctrine,
        protected $localizationParameterName
    ) {
    }

    /**
     * {@inheritdoc}
     * @param $entity
     * @param array $options
     * @return bool
     */
    public function supports($entity, array $options)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @param $entity
     * @param array $options
     * @return array
     */
    public function getParams($entity, array $options)
    {
        $params = [];
        $localizationId = (int)$this->getConfigValue($this->localizationParameterName, $options);
        $localization = $this->doctrine
            ->getManagerForClass(Localization::class)
            ->find(Localization::class, $localizationId);

        if (null === $localization) {
            $localizationAwareEntity = null;
            if ($entity instanceof LocalizationAwareInterface) {
                $localizationAwareEntity = $entity;
            } elseif ($entity instanceof SalesChannelAwareInterface) {
                $localizationAwareEntity = $entity->getSalesChannel();
            }

            if ($this->chainLocalizationProvider && null !== $localizationAwareEntity) {
                $localization = $this->chainLocalizationProvider->getLocalization($localizationAwareEntity);
            }
        }

        if ($localization) {
            $params['localization'] = $localization;
            $params['language'] = $localization->getLanguageCode();
            $params['locale'] = $localization->getLanguageCode();
        }

        return $params;
    }

    /**
     * {@inheritdoc}
     * @param $configKey
     * @param array $options
     * @return mixed
     */
    protected function getConfigValue($configKey, array $options)
    {
        $scopeIdentifier = $options[self::SCOPE_IDENTIFIER_KEY] ?? null;

        return $this->config->get($configKey, false, false, $scopeIdentifier);
    }

    /**
     * {@inheritdoc}
     * @param EntityLocalizationProviderInterface $chainProvider
     */
    public function setChainLocalizationProvider(EntityLocalizationProviderInterface $chainProvider)
    {
        $this->chainLocalizationProvider = $chainProvider;
    }
}
