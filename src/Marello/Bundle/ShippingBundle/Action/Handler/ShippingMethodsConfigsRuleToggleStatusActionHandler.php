<?php

namespace Marello\Bundle\ShippingBundle\Action\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;

class ShippingMethodsConfigsRuleToggleStatusActionHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var boolean
     */
    private $value;

    /**
     * @param EntityManagerInterface $entityManager
     * @param boolean $value
     */
    public function __construct(EntityManagerInterface $entityManager, $value)
    {
        $this->entityManager = $entityManager;
        $this->value = (boolean)$value;
    }

    /**
     * @param ShippingMethodsConfigsRule $configsRule
     * @return boolean
     */
    public function handleAction(ShippingMethodsConfigsRule $configsRule)
    {
        if ($configsRule->getRule()->isSystem()) {
            return false;
        }
        $configsRule->getRule()->setEnabled($this->value);
        $this->entityManager->persist($configsRule);
        $this->entityManager->flush();

        return true;
    }
}
