<?php

namespace Marello\Bundle\PricingBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;
use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;

interface PricingAwareInterface
{
    const CHANNEL_PRICING_STATE_KEY = 'pricing_enabled';
    const CHANNEL_PRICING_DROP_KEY = 'pricing_drop';

    /**
     * Get collection of ProductChannelPrices
     * @return ArrayCollection
     */
    public function getChannelPrices();

    /**
     * Add channel price to collection
     * @param AssembledChannelPriceList $channelPrice
     * @return $this
     */
    public function addChannelPrice(AssembledChannelPriceList $channelPrice);

    /**
     * Remove channel price from collection
     * @param AssembledChannelPriceList $channelPrice
     * @return $this
     */
    public function removeChannelPrice(AssembledChannelPriceList $channelPrice);

    /**
     * @return bool
     */
    public function hasChannelPrices();

    /**
     * Get collection of ProductPrices
     * @return ArrayCollection
     */
    public function getPrices();

    /**
     * Add price to collection
     * @param AssembledPriceList $price
     * @return $this
     */
    public function addPrice(AssembledPriceList $price);

    /**
     * Remove price from collection
     * @param AssembledPriceList $price
     * @return $this
     */
    public function removePrice(AssembledPriceList $price);

    /**
     * @return bool
     */
    public function hasPrices();
}
