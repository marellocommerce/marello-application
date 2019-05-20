<?php

namespace Marello\Bundle\LocaleBundle\Provider;

use Marello\Bundle\LocaleBundle\Model\LocaleAwareInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\LocaleBundle\Entity\Localization;

class DefaultEntityLocalizationProvider implements EntityLocalizationProviderInterface
{
    /**
     * @var  ConfigManager
     */
    private $configManager;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @param ConfigManager $configManager
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(ConfigManager $configManager, DoctrineHelper $doctrineHelper)
    {
        $this->configManager = $configManager;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @inheritDoc
     */
    public function getLocalization(LocaleAwareInterface $entity)
    {
        $defaultId = $this->configManager->get('oro_locale.default_localization');
        if ($defaultId) {
            return $this->doctrineHelper
                ->getEntityManagerForClass(Localization::class)
                ->getRepository(Localization::class)
                ->find($defaultId);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(LocaleAwareInterface $entity)
    {
        return true;
    }
}
