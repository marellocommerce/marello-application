<?php

namespace Marello\Bundle\PricingBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class CurrencyProvider
{
    const CURRENCY_IDENTIFIER = 'currency-';

    public function __construct(
        protected ManagerRegistry $registry,
        protected LocaleSettings $localeSettings
    ) {
    }

    /**
     * Get currency for channel.
     * @param $channelId
     * @return array
     */
    public function getCurrencyDataByChannel($channelId)
    {
        $channel = $this->getRepository(SalesChannel::class)->find($channelId);
        $result[self::CURRENCY_IDENTIFIER.$channel->getId()] = [
            'currencyCode' => $channel->getCurrency(),
            'currencySymbol' => $this->getCurrencySymbol($channel->getCurrency())
        ];

        return $result;
    }

    /**
     * Get currency symbol for currencyCode or entity.
     * @param $currencyCode
     * @return array
     */
    public function getCurrencySymbol($currencyCode)
    {
        return $this->localeSettings->getCurrencySymbolByCurrency($currencyCode);
    }

    /**
     * Get currency data for the given entity
     * returns array in format
     * [
     *      'currencyCode'      => 'EUR',
     *      'currencySymbol'    => '€'
     * ]
     * @param CurrencyAwareInterface $entity
     * @return array
     */
    public function getCurrencyData(CurrencyAwareInterface $entity)
    {
        $result = [
            'currencyCode'      => $entity->getCurrency(),
            'currencySymbol'    => $this->getCurrencySymbol($entity->getCurrency())
        ];

        return $result;
    }

    /**
     * Get available currencies
     * @param $entities
     * @return array
     */
    public function getCurrencies($entities)
    {
        $currencies = [];
        if (!is_array($entities)) {
            $entities
                ->map(function (CurrencyAwareInterface $entity) use (&$currencies) {
                    $currencies[$entity->getCurrency()] = $entity->getCurrency();
                });
        } else {
            foreach ($entities as $entity) {
                if ($entity instanceof CurrencyAwareInterface) {
                    $currencies[$entity->getCurrency()] = $entity->getCurrency();
                }
            }
        }

        return array_unique($currencies);
    }

    /**
     * @param string $className
     * @return EntityRepository
     */
    protected function getRepository($className)
    {
        return $this->registry->getManagerForClass($className)->getRepository($className);
    }
}
