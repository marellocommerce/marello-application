<?php

namespace Marello\Bundle\PricingBundle\Subtotal\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;

class CompositeSubtotalProvider extends AbstractSubtotalProvider implements TotalAwareSubtotalProviderInterface
{
    const NAME = 'oro_pricing.subtotal_total';
    const LABEL = 'marello.pricing.subtotals.total.label';
 
    /**
     * @var SubtotalProviderInterface[]
     */
    protected $providers = [];
    
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubtotal($entity)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException('Function parameter "entity" should be object.');
        }

        $subtotals = [];
        foreach ($this->getSupportedProviders($entity) as $provider) {
            $subtotals[] = $provider->getSubtotal($entity);
        }

        usort($subtotals, function (Subtotal $leftSubtotal, Subtotal $rightSubtotal) {
            return $leftSubtotal->getSortOrder() - $rightSubtotal->getSortOrder();
        });

        return new ArrayCollection($subtotals);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal($entity, iterable $subtotals = [])
    {
        $total = new Subtotal([]);
        
        $total->setType('total')
            ->setLabel($this->translator->trans(self::LABEL))
            ->setCurrency($this->getBaseCurrency($entity))
            ->setVisible(true);

        $totalAmount = 0.0;
        $subtotals = $subtotals ?: $this->getSubtotal($entity);
        foreach ($subtotals as $subtotal) {
            $rowTotal = $subtotal->getAmount();

            $totalAmount = $this->calculateTotal($subtotal->getOperation(), $rowTotal, $totalAmount);
        }
        $total->setAmount($this->rounding->round($totalAmount));

        return $total;
    }

    /**
     * @param int $operation
     * @param float $rowTotal
     * @param float $totalAmount
     *
     * @return float
     */
    protected function calculateTotal($operation, $rowTotal, $totalAmount)
    {
        if ($operation === Subtotal::OPERATION_ADD) {
            $totalAmount += $rowTotal;
        } elseif ($operation === Subtotal::OPERATION_SUBTRACTION) {
            $totalAmount -= $rowTotal;
        }
        if ($totalAmount < 0) {
            $totalAmount = 0.0;
        }

        return $totalAmount;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($entity)
    {
        return true;
    }

    /**
     * Add provider to registry
     *
     * @param SubtotalProviderInterface $provider
     */
    public function addProvider(SubtotalProviderInterface $provider)
    {
        $this->providers[$provider->getName()] = $provider;
    }

    /**
     * Get supported provider list
     *
     * @param $entity
     *
     * @return SubtotalProviderInterface[]
     */
    public function getSupportedProviders($entity)
    {
        $providers = [];
        foreach ($this->providers as $provider) {
            if ($provider->isSupported($entity)) {
                $providers[] = $provider;
            }
        }
        return $providers;
    }


    /**
     * Get all providers
     *
     * @return SubtotalProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Get provider by name
     *
     * @param string $name
     *
     * @return null|SubtotalProviderInterface
     */
    public function getProviderByName($name)
    {
        if ($this->hasProvider($name)) {
            return $this->providers[$name];
        }

        return null;
    }

    /**
     * Check available provider by name
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasProvider($name)
    {
        return array_key_exists($name, $this->providers);
    }
}
