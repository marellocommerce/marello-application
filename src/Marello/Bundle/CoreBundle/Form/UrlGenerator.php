<?php

namespace Marello\Bundle\CoreBundle\Form;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use Oro\Bundle\PlatformBundle\Provider\PackageProvider;

class UrlGenerator
{
    const MARELLO_NAMESPACE = 'marellocommerce';
    const MARELLO_PACKAGE   = 'marello';
    const COMMUNITY_EDITION = 'CE';
    const FIRST_VERSION     = '1.0.0';
    const URL = '//services.marello.com/a/';

    /** @var PackageProvider $packageProvider */
    protected $packageProvider;

    /** @var CacheItemPoolInterface $cacheProvider */
    protected $cacheProvider;

    /** @var RequestStack $requestStack */
    protected $requestStack;

    /**
     * @param PackageProvider $packageProvider
     * @param CacheItemPoolInterface $cacheProvider
     * @param RequestStack $requestStack
     */
    public function __construct(
        PackageProvider $packageProvider,
        CacheItemPoolInterface $cacheProvider,
        RequestStack $requestStack
    ) {
        $this->packageProvider = $packageProvider;
        $this->cacheProvider = $cacheProvider;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getUrl()
    {
        if ($this->cacheProvider->hasItem('marello_url')) {
            return $this->cacheProvider->getItem('marello_url')->get();
        }

        $url = self::URL;
        $editionAndVersion = $this->findEditionAndVersionInPackage();
        $data = array(
            'd' => $this->getSchemeAndHost(),
            'e' => $editionAndVersion['edition'],
            'r' => $editionAndVersion['version'],
            'p' => $this->getServerIpAddress()
        );

        $url .= sprintf('?%s', urlencode(http_build_query($data)));

        $cacheItem ??=$this->cacheProvider->getItem('marello_url');
        $cacheItem->set($url);
        $this->cacheProvider->save($cacheItem);

        return $url;
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    private function findEditionAndVersionInPackage()
    {
        $packages = $this->packageProvider->getThirdPartyPackages();
        $data = [];
        foreach ($packages as $packageName => $package) {
            $packageName = str_replace(
                self::MARELLO_NAMESPACE . PackageProvider::NAMESPACE_DELIMITER,
                '',
                $packageName
            );

            if ($packageName === self::MARELLO_PACKAGE) {
                $data['edition'] = self::COMMUNITY_EDITION;
                $data['version'] = $package['pretty_version'];
                continue;
            }
        }

        if (!isset($data['version'])) {
            $data['version'] = self::FIRST_VERSION;
        }

        if (!isset($data['edition'])) {
            $data['edition'] = self::COMMUNITY_EDITION;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    private function getSchemeAndHost()
    {
        return $this->getCurrentRequest()->getSchemeAndHttpHost();
    }

    /**
     * {@inheritdoc}
     * @return mixed
     */
    private function getServerIpAddress()
    {
        return $this->getCurrentRequest()->server->get('SERVER_ADDR', '');
    }

    /**
     * {@inheritdoc}
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    private function getCurrentRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}
