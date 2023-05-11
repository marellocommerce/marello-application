<?php

namespace Marello\Bundle\InvoiceBundle\Pdf\Logo;

use Marello\Bundle\PdfBundle\DependencyInjection\Configuration;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\AttachmentBundle\Manager\ImageResizeManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class InvoiceLogoPathProvider
{
    const IMAGE_FILTER = 'invoice_logo';

    protected $configManager;

    protected $doctrineHelper;

    protected $attachmentManager;

    protected $imageResizer;

    protected $projectDir;

    public function __construct(
        ConfigManager $configManager,
        DoctrineHelper $doctrineHelper,
        AttachmentManager $attachmentManager,
        ImageResizeManagerInterface $imageResizer,
        $projectDir
    ) {
        $this->configManager = $configManager;
        $this->doctrineHelper = $doctrineHelper;
        $this->attachmentManager = $attachmentManager;
        $this->imageResizer = $imageResizer;
        $this->projectDir = $projectDir;
    }

    /**
     * @param SalesChannel $salesChannel
     * @param bool $absolute
     * @return string|null
     */
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

    /**
     * @param SalesChannel $salesChannel
     * @return mixed
     */
    public function getInvoiceLogoWidth(SalesChannel $salesChannel)
    {
        $key = sprintf('%s.%s', Configuration::CONFIG_NAME, Configuration::CONFIG_KEY_LOGO_WIDTH);

        return $this->configManager->get($key, false, false, $salesChannel);
    }

    /**
     * @param SalesChannel $salesChannel
     * @return mixed
     */
    protected function getInvoiceLogoId(SalesChannel $salesChannel)
    {
        $key = sprintf('%s.%s', Configuration::CONFIG_NAME, Configuration::CONFIG_KEY_LOGO);

        return $this->configManager->get($key, false, false, $salesChannel);
    }


    /**
     * @param $id
     * @return object|null
     */
    protected function getInvoiceLogoEntity($id)
    {
        return $this->doctrineHelper
            ->getEntityRepositoryForClass(File::class)
            ->find($id);
    }

    /**
     * @param File $entity
     * @param $absolute
     * @return string
     */
    protected function getInvoiceLogoAttachment(File $entity, $absolute)
    {
        $path = $this->attachmentManager->getFilteredImageUrl($entity, self::IMAGE_FILTER);
        $phpFiles = ['index.php', 'index_dev.php'];
        foreach ($phpFiles as $phpFile) {
            $path = str_replace($phpFile, '', $path);
        }
        $path = str_replace('//', '/', $path);
        $absolutePath = $this->projectDir . '/public' . $path;

        if (!file_exists($absolutePath)) {
            $this->fetchImage($entity);
        }

        if ($absolute) {
            return $absolutePath;
        }

        return $path;
    }

    /**
     * @param File $entity
     */
    protected function fetchImage(File $entity)
    {
        $this->imageResizer->applyFilter($entity, self::IMAGE_FILTER, '', true);
    }
}
