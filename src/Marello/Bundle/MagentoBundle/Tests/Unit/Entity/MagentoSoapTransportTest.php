<?php

namespace Marello\Bundle\MagentoBundle\Tests\Unit\Entity;

use Marello\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Marello\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class MagentoSoapTransportTest extends AbstractEntityTestCase
{
    /** @var MagentoSoapTransport */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return MagentoSoapTransport::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $apiUrl = 'http://localhost/?wsdl';
        $apiUser = $apiKey = uniqid();
        $syncStartDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $syncRange = \DateInterval::createFromDateString('p1d');
        $websiteId = 123;
        $websites = [];
        $isExtensionInstalled = true;
        $adminUrl = 'http://localhost/admin';

        return [
            'api_url'                => ['apiUrl',               $apiUrl, $apiUrl],
            'api_user'               => ['apiUser',              $apiUser, $apiUser],
            'api_key'                => ['apiKey',               $apiKey, $apiKey],
            'website_id'             => ['websiteId',            $websiteId, $websiteId],
            'websites'               => ['websites',             $websites, $websites],
            'syncStartDate'          => ['syncStartDate',        $syncStartDate, $syncStartDate],
            'syncRange'              => ['syncRange',            $syncRange, $syncRange],
            'is_extension_installed' => ['isExtensionInstalled', $isExtensionInstalled, $isExtensionInstalled],
            'admin_url'              => ['adminUrl',             $adminUrl, $adminUrl],
            'extension_version'      => ['extensionVersion',     '1.0.0', '1.0.0'],
            'magento_version'        => ['magentoVersion',       '1.0.0', '1.0.0'],
            'currency'               => ['currency',             'EUR', 'EUR'],
        ];
    }

    public function testSettingsBag()
    {
        $data = [
            'api_user' => 'test_user',
            'api_key' => 'test_key',
            'api_url' => 'http://test.url/',
            'wsdl_url' => 'http://test.url/',
            'sync_range' => new \DateInterval('P1D'),
            'wsi_mode' => true,
            'website_id' => 1,
            'start_sync_date' => new \DateTime('now'),
            'initial_sync_start_date' => new \DateTime('now'),
            'extension_version' => '1.1.0',
            'magento_version' => '1.8.0.0',
            'currency' => 'EUR'
        ];

        $this->entity
            ->setApiUser($data['api_user'])
            ->setApiKey($data['api_key'])
            ->setApiUrl($data['api_url'])
            ->setSyncRange($data['sync_range'])
            ->setIsWsiMode($data['wsi_mode'])
            ->setWebsiteId($data['website_id'])
            ->setSyncStartDate($data['start_sync_date'])
            ->setInitialSyncStartDate($data['initial_sync_start_date'])
            ->setExtensionVersion('1.1.0')
            ->setMagentoVersion('1.8.0.0')
            ->setCurrency($data['currency']);

        $settingsBag = $this->entity->getSettingsBag();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\ParameterBag', $settingsBag);
        $this->assertSame($settingsBag, $this->entity->getSettingsBag());
        $this->assertEquals($data, $settingsBag->all());
    }

    /**
     * @dataProvider supportDataProvider
     *
     * @param bool $isExtensionInstalled
     * @param string $extensionVersion
     * @param bool $expected
     */
    public function testSupportedExtensionVersion($isExtensionInstalled, $extensionVersion, $expected)
    {
        $this->entity->setExtensionVersion($extensionVersion)
            ->setIsExtensionInstalled($isExtensionInstalled);

        $this->assertEquals($expected, $this->entity->isSupportedExtensionVersion());
    }

    /**
     * @return array
     */
    public function supportDataProvider()
    {
        return [
            [
                true, '0.1', false,
                true, SoapTransport::REQUIRED_EXTENSION_VERSION, true,
                false, '', false
            ]
        ];
    }

    public function testApiUrl()
    {
        $url = 'http://test.local/?wsdl=1';
        $cache = '/tmp/cached.wsdl';
        $this->entity->setApiUrl($url);

        $this->assertEquals($url, $this->entity->getSettingsBag()->get('api_url'));

        $this->entity->setWsdlCachePath($cache);
        $this->assertEquals($cache, $this->entity->getSettingsBag()->get('api_url'));
    }
}
