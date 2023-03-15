<?php

namespace Marello\Bundle\PdfBundle\Provider;

use Marello\Bundle\PdfBundle\DependencyInjection\Configuration;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\AttachmentBundle\Manager\ImageResizeManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class LogoPathProvider
{
    const IMAGE_FILTER = 'pdf_logo';

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
    public function getLogo(SalesChannel $salesChannel, bool $absolute = false)
    {
        $path = null;

        $id = $this->getLogoId($salesChannel);
        if ($id !== null) {
            $entity = $this->getLogoEntity($id);
            if ($entity !== null) {
                $path = $this->getLogoAttachment($entity, $absolute);
            }
        }

        return $path;
    }

    /**
     * @param SalesChannel $salesChannel
     * @return mixed
     */
    public function getLogoWidth(SalesChannel $salesChannel)
    {
        $key = sprintf('%s.%s', Configuration::CONFIG_NAME, Configuration::CONFIG_KEY_LOGO_WIDTH);

        return $this->configManager->get($key, false, false, $salesChannel);
    }

    /**
     * @param SalesChannel $salesChannel
     * @return mixed
     */
    protected function getLogoId(SalesChannel $salesChannel)
    {
        $key = sprintf('%s.%s', Configuration::CONFIG_NAME, Configuration::CONFIG_KEY_LOGO);

        return $this->configManager->get($key, false, false, $salesChannel);
    }


    /**
     * @param $id
     * @return object|null
     */
    protected function getLogoEntity($id)
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
    protected function getLogoAttachment(File $entity, $absolute)
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
            return $path = $absolutePath;
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
