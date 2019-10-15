<?php

namespace Marello\Bundle\PaymentBundle\Action\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;

class PaymentMethodsConfigsRuleToggleStatusActionHandler
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
     * @param PaymentMethodsConfigsRule $configsRule
     * @return boolean
     */
    public function handleAction(PaymentMethodsConfigsRule $configsRule)
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
