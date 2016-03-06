<?php

namespace Marello\Bundle\PricingBundle\Model;

use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;

interface PricingAwareInterface
{
    const CHANNEL_PRICING_STATE_KEY = 'pricing_enabled';
    const CHANNEL_PRICING_DROP_KEY = 'pricing_drop';

    public function getChannelPrices();

    public function addChannelPrice(ProductChannelPrice $channelPrice);

    public function removeChannelPrice(ProductChannelPrice $channelPrice);

    public function getPrices();

    public function addPrice(ProductPrice $price);

    public function removePrice(ProductPrice $price);
}
