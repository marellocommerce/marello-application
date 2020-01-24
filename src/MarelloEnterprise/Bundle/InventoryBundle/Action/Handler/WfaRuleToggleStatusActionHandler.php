<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Action\Handler;

use Doctrine\ORM\EntityManagerInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;

class WfaRuleToggleStatusActionHandler
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
     * @param WFARule $wfaRule
     * @return boolean
     */
    public function handleAction(WFARule $wfaRule)
    {
        if ($wfaRule->getRule()->isSystem()) {
            return false;
        }
        $wfaRule->getRule()->setEnabled($this->value);
        $this->entityManager->persist($wfaRule);
        $this->entityManager->flush();

        return true;
    }
}
