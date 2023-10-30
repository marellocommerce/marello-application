<?php

namespace Marello\Bundle\ProductBundle\Async;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\AttachmentBundle\Manager\ImageResizeManagerInterface;

use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Async\Topic\ProductImageUpdateTopic;

class ProductImageUpdateProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private ConfigManager $configManager,
        private AttachmentManager $attachmentManager,
        private ImageResizeManagerInterface $imageResizeManager
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [ProductImageUpdateTopic::getName()];
    }

    /**
     * @param MessageInterface $message
     * @param SessionInterface $session
     * @return string
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        if (!$this->configManager->get('marello_product.image_use_external_url')) {
            return self::REJECT;
        }

        $data = JSON::decode($message->getBody());
        /** @var Product $product */
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['id' => $data['productId']]);
        if ($product && $product->getImage()) {
            try {
                $image = $product->getImage();
                /** @var File $file */
                $file = $this->entityManager->getRepository(File::class)->find($image->getId());
                // media url is an extended field, so it will not 'show up' in the auto complete
                if (Product::class === $file->getParentEntityClass()) {
                    $url = $this->attachmentManager
                        ->getFilteredImageUrl($file, 'product_view');
                    $file->setMediaUrl($url);
                    $this->entityManager->persist($file);
                    $this->entityManager->flush($file);
                    $this->imageResizeManager->applyFilter($file, 'product_view');
                }
            } catch (\Exception $e) {
                $this->logger->error(
                    'Unexpected exception occurred during updating Media Url for Product Image File',
                    ['exception' => $e]
                );

                return self::REJECT;
            }

            return self::ACK;
        }

        return self::REJECT;
    }
}
