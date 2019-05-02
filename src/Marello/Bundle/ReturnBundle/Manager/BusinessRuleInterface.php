<?php

namespace Marello\Bundle\ReturnBundle\Manager;

interface BusinessRuleInterface
{
    public function isApplicable();

    public function applyRule($entity);
}
