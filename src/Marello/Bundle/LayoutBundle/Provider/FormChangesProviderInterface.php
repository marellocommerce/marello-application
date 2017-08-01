<?php

namespace Marello\Bundle\LayoutBundle\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;

interface FormChangesProviderInterface
{
    /**
     * @param FormChangeContextInterface $context
     */
    public function processFormChanges(FormChangeContextInterface $context);
}
