<?php

namespace Marello\Bundle\PaymentBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRuleDestinationPostalCode;
use Symfony\Component\Form\DataTransformerInterface;

class DestinationPostalCodeTransformer implements DataTransformerInterface
{
    /**
     * @param ArrayCollection|PaymentMethodsConfigsRuleDestinationPostalCode[] $postalCodes
     * @return string
     */
    public function transform($postalCodes)
    {
        if (!$postalCodes) {
            return '';
        }

        $postalCodesString = '';
        foreach ($postalCodes as $postalCode) {
            $postalCodesString .= $postalCode->getName() . ', ';
        }
        $postalCodesString = rtrim($postalCodesString, ', ');

        return $postalCodesString;
    }

    /**
     * @param string|null $postalCodesString
     * @return ArrayCollection|PaymentMethodsConfigsRuleDestinationPostalCode[]
     */
    public function reverseTransform($postalCodesString)
    {
        $postalCodes = new ArrayCollection();

        if (!$postalCodesString || $postalCodesString === '') {
            return $postalCodes;
        }

        $postalCodeNames = explode(',', $postalCodesString);
        foreach ($postalCodeNames as $postalCodeName) {
            $postalCode = new PaymentMethodsConfigsRuleDestinationPostalCode();

            $postalCode->setName(trim($postalCodeName));
            $postalCodes->add($postalCode);
        }

        return $postalCodes;
    }
}
