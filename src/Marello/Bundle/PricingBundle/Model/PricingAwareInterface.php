<?php

namespace Marello\Bundle\PricingBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;

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
     * @param ProductChannelPrice $channelPrice
     * @return mixed
     */
    public function addChannelPrice(ProductChannelPrice $channelPrice);

    /**
     * Remove channel price from collection
     * @param ProductChannelPrice $channelPrice
     * @return mixed
     */
    public function removeChannelPrice(ProductChannelPrice $channelPrice);

    /** @return bool */
    public function hasChannelPrices();

    /**
     * Get collection of ProductPrices
     * @return ArrayCollection
     */
    public function getPrices();

    /**
     * Add price to collection
     * @param ProductPrice $price
     * @return mixed
     */
    public function addPrice(ProductPrice $price);

    /**
     * Remove price from collection
     * @param ProductPrice $price
     * @return mixed
     */
    public function removePrice(ProductPrice $price);

    /** @return bool */
    public function hasPrices();
}
