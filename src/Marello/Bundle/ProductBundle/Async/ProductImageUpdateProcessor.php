<?php

namespace Marello\Bundle\ProductBundle\Async;

use Doctrine\ORM\EntityManagerInterface;

use Doctrine\Persistence\Proxy;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Psr\Log\LoggerInterface;

use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

use Marello\Bundle\ProductBundle\Entity\Product;

class ProductImageUpdateProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    const TOPIC = 'marello_product.product_image_update';

    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private ConfigManager $configManager,
        private AttachmentManager $attachmentManager
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [self::TOPIC];
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
        $productSku = $data['productSku'];
        /** @var Product $product */
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['sku' => $productSku]);
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
