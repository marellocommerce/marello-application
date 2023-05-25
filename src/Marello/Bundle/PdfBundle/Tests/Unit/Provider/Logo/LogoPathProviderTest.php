<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Provider\Logo;

use Doctrine\ORM\EntityRepository;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Marello\Bundle\PdfBundle\Provider\LogoPathProvider;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\AttachmentBundle\Manager\ImageResizeManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class LogoPathProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @param $salesChannel
     * @param $absolute
     * @param $configId
     * @param $logoEntity
     * @param $resizedPath
     * @param $returnPath
     *
     * @dataProvider getLogoProvider
     */
    public function testGetLogo(
        $salesChannel,
        $absolute,
        $configId,
        $logoEntity,
        $resizedPath,
        $returnPath
    ) {
        /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->once())
            ->method('get')
            ->with('marello_pdf.logo', false, false, $salesChannel)
            ->willReturn($configId)
        ;

        /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelper */
        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        if ($configId !== null) {
            /** @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject $repository */
            $repository = $this->createMock(EntityRepository::class);
            $repository->expects($this->once())
                ->method('find')
                ->with($configId)
                ->willReturn($logoEntity)
            ;

            $doctrineHelper->expects($this->once())
                ->method('getEntityRepositoryForClass')
                ->with(File::class)
                ->willReturn($repository)
            ;
        }

        /** @var AttachmentManager|\PHPUnit\Framework\MockObject\MockObject $attachmentManager */
        $attachmentManager = $this->createMock(AttachmentManager::class);
        /** @var ImageResizeManagerInterface|\PHPUnit\Framework\MockObject\MockObject $imageResizer */
        $imageResizer = $this->createMock(ImageResizeManagerInterface::class);
        if ($logoEntity !== null) {
            $attachmentManager->expects($this->once())
                ->method('getFilteredImageUrl')
                ->with($logoEntity, LogoPathProvider::IMAGE_FILTER)
                ->willReturn($resizedPath)
            ;

            if (!file_exists(__DIR__.'/data/public'.$resizedPath)) {
                /** @var BinaryInterface|\PHPUnit\Framework\MockObject\MockObject $resizedImage */
                $resizedImage = $this->createMock(BinaryInterface::class);

                $imageResizer->expects($this->once())
                    ->method('applyFilter')
                    ->with($logoEntity, LogoPathProvider::IMAGE_FILTER)
                    ->willReturn($resizedImage)
                ;
            }
        }

        $provider = new LogoPathProvider(
            $configManager,
            $doctrineHelper,
            $attachmentManager,
            $imageResizer,
            __DIR__.'/data'
        );

        $this->assertEquals($returnPath, $provider->getLogo($salesChannel, $absolute));
    }

    public function getLogoProvider()
    {
        $salesChannel = $this->getEntity(SalesChannel::class, ['name' => 'Sales Channel 1', 'code' => 'test-1']);
        $logoEntity = $this->getEntity(File::class, []);

        return [
            'not existing not set absolute' => [
                'salesChannel' => $salesChannel,
                'absolute' => true,
                'configId' => null,
                'logoEntity' => null,
                'resizedPath' => null,
                'returnPath' => null,
            ],
            'not existing absolute' => [
                'salesChannel' => $salesChannel,
                'absolute' => true,
                'configId' => 12,
                'logoEntity' => null,
                'resizedPath' => null,
                'returnPath' => null,
            ],
            'existing cached absolute' => [
                'salesChannel' => $salesChannel,
                'absolute' => true,
                'configId' => 12,
                'logoEntity' => $logoEntity,
                'resizedPath' => '/resized_image/logoprovider_resized.txt',
                'returnPath' => __DIR__.'/data/public/resized_image/logoprovider_resized.txt',
            ],
            'existing cached relative' => [
                'salesChannel' => $salesChannel,
                'absolute' => false,
                'configId' => 12,
                'logoEntity' => $logoEntity,
                'resizedPath' => '/resized_image/logoprovider_resized.txt',
                'returnPath' => '/resized_image/logoprovider_resized.txt',
            ],
            'existing not cached absolute' => [
                'salesChannel' => $salesChannel,
                'absolute' => true,
                'configId' => 12,
                'logoEntity' => $logoEntity,
                'resizedPath' => '/resized_image/logoprovider_resized_not_found.txt',
                'returnPath' => __DIR__.'/data/public/resized_image/logoprovider_resized_not_found.txt',
            ],
            'existing not cached relative' => [
                'salesChannel' => $salesChannel,
                'absolute' => false,
                'configId' => 12,
                'logoEntity' => $logoEntity,
                'resizedPath' => '/resized_image/logoprovider_resized_not_found.txt',
                'returnPath' => '/resized_image/logoprovider_resized_not_found.txt',
            ],
        ];
    }
}
