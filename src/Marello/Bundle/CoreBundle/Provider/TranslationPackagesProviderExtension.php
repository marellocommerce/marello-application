<?php

namespace Marello\Bundle\CoreBundle\Provider;

use Symfony\Component\Config\FileLocator;

use Oro\Bundle\TranslationBundle\Provider\TranslationPackagesProviderExtensionInterface;

class TranslationPackagesProviderExtension implements TranslationPackagesProviderExtensionInterface
{
    const PACKAGE_NAME = 'Marello';

    /**
     * {@inheritdoc}
     */
    public function getPackageNames()
    {
        return [self::PACKAGE_NAME];
    }

    /**
     * {@inheritdoc}
     */
    public function getPackagePaths()
    {
        return new FileLocator(__DIR__ . '/../../../../');
    }
}
