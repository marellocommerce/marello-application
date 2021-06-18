<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Yaml\Yaml;

class LoadPdfConfiguration extends AbstractFixture implements ContainerAwareInterface
{
    const DATA_FILE_PATH = '/data/pdf_settings.yml';
    const LOGO_PATH = '/images/goodwaves-invoice-logo.png';

    use ContainerAwareTrait;

    protected $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $data = Yaml::parse(file_get_contents(__DIR__.self::DATA_FILE_PATH));

        $this->loadGlobalConfig($data);
        $this->loadSalesChannelConfig($data);
        $this->loadInvoiceLogo();
    }

    protected function loadInvoiceLogo()
    {
        $file = $this->container->get('oro_attachment.file_manager')->createFileEntity(__DIR__.self::LOGO_PATH);
        $this->manager->persist($file);
        $this->manager->flush($file);

        $this->getConfigManager(null)->set('marello_pdf.logo', $file->getId(), null);
        $this->getConfigManager()->flush();
    }

    protected function loadGlobalConfig(array $data)
    {
        $this->loadConfig($data['configuration']['global'], null);
    }

    protected function loadSalesChannelConfig(array $data)
    {
        $salesChannel = $this->loadSalesChannel();

        $this->loadConfig($data['configuration']['sales_channel'], $salesChannel);
    }

    protected function loadConfig(array $config, $scope = null)
    {
        foreach ($config as $section => $sectionConfig) {
            foreach ($sectionConfig as $key => $value) {
                $this->setConfigValue($section, $key, $value, $scope);
            }
        }

        $this->getConfigManager($scope)->flush($scope);
    }

    protected function setConfigValue($section, $key, $value, $scope)
    {
        $this->getConfigManager($scope)->set(sprintf('%s.%s', $section, $key), $value, $scope);
    }

    protected function getConfigManager($scope = null)
    {
        if ($scope === null) {
            return $this->container->get('oro_config.global');
        }
        if ($scope instanceof SalesChannel) {
            return $this->container->get('oro_config.saleschannel');
        }
    }

    protected function loadSalesChannel()
    {
        return $this->manager
            ->getRepository(SalesChannel::class)
            ->findOneBy([
                'code' => 'main',
            ])
        ;
    }
}
