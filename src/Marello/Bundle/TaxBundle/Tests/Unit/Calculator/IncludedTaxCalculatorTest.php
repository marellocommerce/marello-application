<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Calculator;

use Marello\Bundle\TaxBundle\Calculator\IncludedTaxCalculator;

class IncludedTaxCalculatorTest extends AbstractTaxCalculatorTest
{
    /**
     * @return array
     */
    public function calculateDataProvider()
    {
        return [
            // use cases
            'Finney County' => [['17.21', '15.99', '1.22'], '17.21', '0.0765'],
            'Fremont County' => [['59.04', '56.23', '2.81'], '59.04', '0.05'],
            'Tulare County' => [['14.41', '13.34', '1.07'], '14.41', '0.08'],
            'Mclean County' => [['35.88', '33.77', '2.11'], '35.88', '0.0625'],

            // edge cases
            [['15.98', '7.99', '7.99'], '15.98', '1'],
            [['15.98', '5.33', '10.65'], '15.98', '2'],
            [['15.98', '8.03', '7.95'], '15.98', '0.99'],
            [['15.98', '15.96', '0.02'], '15.98', '0.001'],
            [['15.98', '15.96', '0.02'], '15.98', '0.0015'],
            [['15.98', '13.32', '2.66'], '15.98', '-0.2'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCalculator()
    {
        return new IncludedTaxCalculator($this->rounding);
    }
}
