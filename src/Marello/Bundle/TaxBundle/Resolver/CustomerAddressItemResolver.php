<?php

namespace Marello\Bundle\TaxBundle\Resolver;

use Marello\Bundle\TaxBundle\Model\Taxable;

class CustomerAddressItemResolver extends AbstractItemResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve(Taxable $taxable)
    {
        if ($taxable->getItems()->count()) {
            return;
        }

        if (!$taxable->getPrice()) {
            return;
        }

        $address = $taxable->getTaxationAddress();
        if (!$address) {
            return;
        }

        if ($taxable->getResult()->isResultLocked()) {
            return;
        }

        $taxRule = $this->matcher->match($address, [$taxable->getTaxCode()->getCode()]);
        $taxableAmount = (float)$taxable->getPrice();

        $result = $taxable->getResult();
        $this->rowTotalResolver->resolveRowTotal($result, $taxableAmount, $taxable->getQuantity(), $taxRule);
        $result->lockResult();
    }
}
