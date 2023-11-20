<?php

namespace Marello\Bundle\SalesBundle\Twig;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SalesExtension extends AbstractExtension
{
    const NAME = 'marello_sales';

    /**
     * @var SalesChannelRepository
     */
    private $salesChannelRepository;

    public function __construct(
        private ManagerRegistry $registry,
        private AclHelper $aclHelper
    ) {
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_sales_has_active_channels',
                [$this, 'checkActiveChannels']
            ),
            new TwigFunction(
                'marello_get_sales_channel_name_by_code',
                [$this, 'getChannelNameByCode']
            ),
            new TwigFunction(
                'marello_get_product_ids_by_channel_ids',
                [$this, 'getProductIdsByChannelIds']
            )
        ];
    }

    /**
     * @return boolean
     */
    public function checkActiveChannels(): bool
    {
        if ($this->getRepository()->getActiveChannels($this->aclHelper)) {
            return true;
        }
        
        return false;
    }

    /**
     * @param array $channelIds
     * @return array
     */
    public function getProductIdsByChannelIds(array $channelIds): array
    {
        $productRepository = $this->registry->getRepository(Product::class);

        return $productRepository->getProductIdsBySalesChannelIds($channelIds, $this->aclHelper);
    }

    /**
     * @param string $code
     * @return string
     */
    public function getChannelNameByCode($code): string
    {
        $channel = $this->getRepository()
            ->findOneBy(['code' => $code]);
        if ($channel) {
            return $channel->getName();
        }

        return $code;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName(): string
    {
        return self::NAME;
    }

    protected function getRepository(): SalesChannelRepository
    {
        if (!$this->salesChannelRepository) {
            $this->salesChannelRepository = $this->registry->getRepository(SalesChannel::class);
        }

        return $this->salesChannelRepository;
    }
}
