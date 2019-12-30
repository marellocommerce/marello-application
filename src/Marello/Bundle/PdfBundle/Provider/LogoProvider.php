<?php

namespace Marello\Bundle\PdfBundle\Provider;

use Marello\Bundle\PdfBundle\DependencyInjection\Configuration;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\AttachmentBundle\Manager\MediaCacheManagerRegistryInterface;
use Oro\Bundle\AttachmentBundle\Manager\ImageResizeManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class LogoProvider
{
    const IMAGE_FILTER = 'invoice_logo';

    protected $configManager;

    protected $doctrineHelper;

    protected $attachmentManager;

    protected $mediaCacheManager;

    protected $imageResizer;

    protected $projectDir;

    public function __construct(
        ConfigManager $configManager,
        DoctrineHelper $doctrineHelper,
        AttachmentManager $attachmentManager,
        MediaCacheManagerRegistryInterface $mediaCacheManager,
        ImageResizeManagerInterface $imageResizer,
        $projectDir
    ) {
        $this->configManager = $configManager;
        $this->doctrineHelper = $doctrineHelper;
        $this->attachmentManager = $attachmentManager;
        $this->mediaCacheManager = $mediaCacheManager;
        $this->imageResizer = $imageResizer;
        $this->projectDir = $projectDir;
    }

    public function getInvoiceLogo(SalesChannel $salesChannel, $absolute = false)
    {
        $path = null;

        $id = $this->getInvoiceLogoId($salesChannel);
        if ($id !== null) {
            $entity = $this->getInvoiceLogoEntity($id);
            if ($entity !== null) {
                $path = $this->getInvoiceLogoAttachment($entity, $absolute);
            }
        }

        return $path;
    }

    protected function getInvoiceLogoId(SalesChannel $salesChannel)
    {
        $key = sprintf('%s.%s', Configuration::CONFIG_NAME, Configuration::CONFIG_KEY_LOGO);

        return $this->configManager->get($key, false, false, $salesChannel);
    }

    protected function getInvoiceLogoEntity($id)
    {
        return $this->doctrineHelper
            ->getEntityRepositoryForClass(File::class)
            ->find($id)
        ;
    }

    protected function getInvoiceLogoAttachment(File $entity, $absolute)
    {
        $path = $this->attachmentManager->getFilteredImageUrl($entity, self::IMAGE_FILTER);
        $absolutePath = $this->projectDir.'/public'.$path;

        if (!file_exists($absolutePath)) {
            $this->fetchImage($entity, $path);
        }

        if ($absolute) {
            return $path = $absolutePath;
        }

        return $path;
    }

    protected function fetchImage(File $entity, $path)
    {
        $this->imageResizer->applyFilter($entity, self::IMAGE_FILTER);
    }
}
