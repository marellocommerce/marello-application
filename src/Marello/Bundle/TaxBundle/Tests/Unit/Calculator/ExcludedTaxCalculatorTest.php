<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Calculator;

use Marello\Bundle\TaxBundle\Calculator\ExcludedTaxCalculator;

class ExcludedTaxCalculatorTest extends AbstractTaxCalculatorTest
{
    /**
     * @return array
     *
     * @link http://salestax.avalara.com/
     */
    public function calculateDataProvider()
    {
        return [
            // use cases
            'Finney County' => [['18.53', '17.21', '1.32'], '17.21', '0.0765'],
            'Fremont County' => [['61.99', '59.04', '2.95'], '59.04', '0.05'],
            'Tulare County' => [['15.56', '14.41', '1.15'], '14.41', '0.08'],
            'Mclean County' => [['38.12', '35.88', '2.24'], '35.88', '0.0625'],

            // edge cases
            [['31.96', '15.98', '15.98'], '15.98', '1'],
            [['47.94', '15.98', '31.96'], '15.98', '2'],
            [['31.80', '15.98', '15.82'], '15.98', '0.99'],
            [['16.00', '15.98', '0.02'], '15.98', '0.001'],
            [['16.00', '15.98', '0.02'], '15.98', '0.0015'],
            [['19.18', '15.98', '3.20'], '15.98', '-0.2'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCalculator()
    {
        return new ExcludedTaxCalculator($this->rounding);
    }
}
