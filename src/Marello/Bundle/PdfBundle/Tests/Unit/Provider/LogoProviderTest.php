<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityRepository;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Marello\Bundle\PdfBundle\Provider\LogoProvider;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\AttachmentBundle\Manager\MediaCacheManager;
use Oro\Bundle\AttachmentBundle\Resizer\ImageResizer;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class LogoProviderTest extends TestCase
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
     * @dataProvider getInvoiceLogoProvider
     */
    public function testGetInvoiceLogo(
        $salesChannel,
        $absolute,
        $configId,
        $logoEntity,
        $resizedPath,
        $returnPath
    ) {
        /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject $configManager */
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->once())
            ->method('get')
            ->with('marello_pdf.logo', false, false, $salesChannel)
            ->willReturn($configId)
        ;

        /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject $doctrineHelper */
        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        if ($configId !== null) {
            /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject $repository */
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

        /** @var AttachmentManager|\PHPUnit_Framework_MockObject_MockObject $attachmentManager */
        $attachmentManager = $this->createMock(AttachmentManager::class);
        /** @var ImageResizer|\PHPUnit_Framework_MockObject_MockObject $imageResizer */
        $imageResizer = $this->createMock(ImageResizer::class);
        /** @var MediaCacheManager|\PHPUnit_Framework_MockObject_MockObject $mediaCacheManager */
        $mediaCacheManager = $this->createMock(MediaCacheManager::class);
        if ($logoEntity !== null) {
            $attachmentManager->expects($this->once())
                ->method('getFilteredImageUrl')
                ->with($logoEntity, LogoProvider::IMAGE_FILTER)
                ->willReturn($resizedPath)
            ;

            if (!file_exists(__DIR__.'/data/public'.$resizedPath)) {
                /** @var BinaryInterface|\PHPUnit_Framework_MockObject_MockObject $resizedImage */
                $resizedImage = $this->createMock(BinaryInterface::class);
                $resizedImage->expects($this->once())
                    ->method('getContent')
                    ->willReturn(file_get_contents(__DIR__.'/data/public/resized_image/logoprovider_resized.txt'))
                ;

                $imageResizer->expects($this->once())
                    ->method('resizeImage')
                    ->with($logoEntity, LogoProvider::IMAGE_FILTER)
                    ->willReturn($resizedImage)
                ;

                $mediaCacheManager->expects($this->once())
                    ->method('store')
                    ->with(
                        file_get_contents(__DIR__.'/data/public/resized_image/logoprovider_resized.txt'),
                        $resizedPath
                    )
                ;
            }
        }

        $provider = new LogoProvider(
            $configManager,
            $doctrineHelper,
            $attachmentManager,
            $mediaCacheManager,
            $imageResizer,
            __DIR__.'/data'
        );

        $this->assertEquals($returnPath, $provider->getInvoiceLogo($salesChannel, $absolute));
    }

    public function getInvoiceLogoProvider()
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
