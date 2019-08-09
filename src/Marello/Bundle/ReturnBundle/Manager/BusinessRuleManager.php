<?php

namespace Marello\Bundle\ReturnBundle\Manager;

class BusinessRuleManager
{
    /** @var ReturnBusinessRuleRegistry $businessRuleregistry */
    protected $businessRuleregistry;

    /**
     * BusinessRuleManager constructor.
     *
     * @param ReturnBusinessRuleRegistry $businessRuleregistry
     */
    public function __construct(ReturnBusinessRuleRegistry $businessRuleregistry)
    {
        $this->businessRuleregistry = $businessRuleregistry;
    }

    /**
     * Apply all available business rules from registry
     * @param $entity
     */
    public function applyRules($entity)
    {
        $businessRules = $this->businessRuleregistry->getBusinessRules();

        $businessRules
            ->map(function (BusinessRuleInterface $rule) use ($entity) {
                $rule->applyRule($entity);
            });
    }
}
