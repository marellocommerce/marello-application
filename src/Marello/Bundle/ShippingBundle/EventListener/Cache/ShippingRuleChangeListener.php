<?php

namespace Marello\Bundle\ShippingBundle\EventListener\Cache;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\RuleBundle\Entity\RuleInterface;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
use Marello\Bundle\ShippingBundle\Provider\Cache\ShippingPriceCache;

class ShippingRuleChangeListener
{
    /**
     * @var  ShippingPriceCache
     */
    private $priceCache;

    /**
     * @var  boolean
     */
    private $executed = false;

    /**
     * @param ShippingPriceCache $priceCache
     */
    public function __construct(ShippingPriceCache $priceCache)
    {
        $this->priceCache = $priceCache;
    }

    /**
     * @param RuleInterface|ShippingMethodsConfigsRule|ShippingMethodConfig|ShippingMethodTypeConfig $entity
     * @param LifecycleEventArgs $args
     */
    public function postPersist($entity, LifecycleEventArgs $args)
    {
        $this->invalidateCache($entity, $args);
    }

    /**
     * @param RuleInterface|ShippingMethodsConfigsRule|ShippingMethodConfig|ShippingMethodTypeConfig $entity
     * @param LifecycleEventArgs $args
     */
    public function postUpdate($entity, LifecycleEventArgs $args)
    {
        $this->invalidateCache($entity, $args);
    }

    /**
     * @param RuleInterface|ShippingMethodsConfigsRule|ShippingMethodConfig|ShippingMethodTypeConfig $entity
     * @param LifecycleEventArgs $args
     */
    public function postRemove($entity, LifecycleEventArgs $args)
    {
        $this->invalidateCache($entity, $args);
    }

    /**
     * @param RuleInterface $rule
     * @param LifecycleEventArgs $args
     *
     * @return boolean
     */
    protected function isShippingRule(RuleInterface $rule, LifecycleEventArgs $args)
    {
        $repository = $args->getObjectManager()->getRepository(ShippingMethodsConfigsRule::class);
        if ($repository->findOneBy(['rule' => $rule])) {
            return true;
        }
        return false;
    }

    /**
     * @param RuleInterface|ShippingMethodsConfigsRule|ShippingMethodConfig|ShippingMethodTypeConfig $entity
     * @param LifecycleEventArgs $args
     */
    protected function invalidateCache($entity, LifecycleEventArgs $args)
    {
        if (!$this->executed) {
            if (!($entity instanceof Rule) || ($entity instanceof Rule && $this->isShippingRule($entity, $args))) {
                $this->priceCache->deleteAllPrices();
                $this->executed = true;
            }
        }
    }
}
