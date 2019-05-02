<?php

namespace Marello\Bundle\TaxBundle\Resolver;

use Marello\Bundle\TaxBundle\Model\Result;
use Marello\Bundle\TaxBundle\Model\ResultElement;
use Marello\Bundle\TaxBundle\Model\Taxable;

class TotalResolver implements ResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(Taxable $taxable)
    {
        if (!$taxable->getItems()->count()) {
            return;
        }

        $data = ResultElement::create(0.0, 0.0, 0.0);

        foreach ($taxable->getItems() as $taxableItem) {
            $taxableItemResult = $taxableItem->getResult();
            $row = $taxableItemResult->getRow();

            try {
                $mergedData = $this->mergeData($data, $row);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
            $data = $mergedData;
        }
        $data = $this->mergeShippingData($taxable, $data);

        $result = $taxable->getResult();
        $result->offsetSet(Result::TOTAL, $data);
    }

    /**
     * @param ResultElement $target
     * @param ResultElement $source
     * @return ResultElement
     */
    protected function mergeData(ResultElement $target, ResultElement $source)
    {
        if ($source->getIncludingTax() < $source->getExcludingTax()) {
            return $target;
        }
        $currentData = new ResultElement($target->getArrayCopy());

        foreach ($source as $key => $value) {
            if ($currentData->offsetExists($key)) {
                $currentValue = (float)$currentData->offsetGet($key);
                $currentValue = $currentValue + (float)$value;
                $currentData->offsetSet($key, (string)$currentValue);
            }
        }

        return $currentData;
    }

    /**
     * @param Taxable $taxable
     * @param ResultElement $target
     * @return ResultElement
     */
    protected function mergeShippingData(Taxable $taxable, ResultElement $target)
    {
        if (!$taxable->getResult()->offsetExists(Result::SHIPPING)) {
            return $target;
        }

        $resultElement = $taxable->getResult()->offsetGet(Result::SHIPPING);

        return $this->mergeData($target, $resultElement);
    }
}
