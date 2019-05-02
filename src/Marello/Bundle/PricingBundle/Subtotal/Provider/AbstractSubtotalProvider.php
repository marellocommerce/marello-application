<?php

namespace Marello\Bundle\PricingBundle\Subtotal\Provider;

use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractSubtotalProvider implements SubtotalProviderInterface
{
    /**
     * array
     */
    protected $dependOnProviders = [];
    
    /**
     * @var string
     */
    protected $baseCurrency;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var RoundingServiceInterface
     */
    protected $rounding;

    /**
     * @var DefaultCurrencyProviderInterface
     */
    protected $defaultCurrencyProvider;

    /**
     * @param TranslatorInterface $translator
     * @param RoundingServiceInterface $rounding
     * @param DefaultCurrencyProviderInterface $defaultCurrencyProvider
     */
    public function __construct(
        TranslatorInterface $translator,
        RoundingServiceInterface $rounding,
        DefaultCurrencyProviderInterface $defaultCurrencyProvider
    ) {
        $this->translator = $translator;
        $this->rounding = $rounding;
        $this->defaultCurrencyProvider = $defaultCurrencyProvider;
    }
    
    public function addDependOnProvider(SubtotalProviderInterface $provider, $operation)
    {
        if (!in_array($operation, [
            Subtotal::OPERATION_ADD,
            Subtotal::OPERATION_SUBTRACTION,
            Subtotal::OPERATION_IGNORE
        ])) {
            throw new \Exception('Not existing operation selected');
        }
        $this->dependOnProviders[$provider->getName()] = [
            'operation' => $operation,
            'provider' => $provider,
        ];
    }

    /**
     * @param $entity
     * @return string
     */
    protected function getBaseCurrency($entity)
    {
        if (!$entity instanceof CurrencyAwareInterface || !$entity->getCurrency()) {
            return $this->defaultCurrencyProvider->getDefaultCurrency();
        } else {
            return $entity->getCurrency();
        }
    }

    /**
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    protected function getExchangeRate($fromCurrency, $toCurrency)
    {
        /**
         * TODO: Need to define currency exchange logic for enterprise version
         */
        return 1.0;
    }
}
