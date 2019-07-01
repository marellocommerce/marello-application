<?php

namespace Marello\Bundle\LocaleBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\LocaleBundle\Provider\DefaultEntityLocalizationProvider;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use PHPUnit\Framework\TestCase;

class DefaultEntityLocalizationProviderTest extends TestCase
{
    public function testGetLocalization()
    {
        $configManager = $this->createMock(ConfigManager::class);
        $configManager
            ->expects(static::once())
            ->method('get')
            ->with('oro_locale.default_localization')
            ->willReturn(1);
        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects(static::once())
            ->method('find')
            ->with(1);
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager
            ->expects(static::once())
            ->method('getRepository')
            ->with(Localization::class)
            ->willReturn($repository);
        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper
            ->expects(static::once())
            ->method('getEntityManagerForClass')
            ->with(Localization::class)
            ->willReturn($entityManager);
        $localizationProvider = new DefaultEntityLocalizationProvider($configManager, $doctrineHelper);
        $localizationProvider->getLocalization(new Order());
    }
}
