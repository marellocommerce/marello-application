<?php

namespace Marello\Bundle\UPSBundle\Factory;

use Marello\Bundle\UPSBundle\Model\Request\UPSRequestInterface;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Model\Package;

abstract class AbstractUPSRequestFactory implements UPSRequestFactoryInterface
{
    const MAX_PACKAGE_WEIGHT_KGS = 70;
    const MAX_PACKAGE_WEIGHT_LBS = 150;

    /**
     * @var SymmetricCrypterInterface
     */
    protected $symmetricCrypter;

    /**
     * @param SymmetricCrypterInterface $symmetricCrypter
     */
    public function __construct(SymmetricCrypterInterface $symmetricCrypter)
    {
        $this->symmetricCrypter = $symmetricCrypter;
    }

    /**
     * @return string
     */
    abstract protected function getRequestClass();

    /**
     * {@inheritdoc}
     */
    public function create(
        UPSSettings $transport,
        ShippingContextInterface $context,
        array $extraParameters = [],
        ShippingService $shippingService = null
    ) {
        if ($this->getRequestClass() === null) {
            return null;
        }
        
        if (!$context->getShippingAddress()) {
            return null;
        }

        $decryptedPassword = $this->symmetricCrypter->decryptData($transport->getUpsApiPassword());
        $requestClass = $this->getRequestClass();
        
        /** @var UPSRequestInterface $request */
        $request = new $requestClass();
        $request
            ->setUsername($transport->getUpsApiUser())
            ->setPassword($decryptedPassword)
            ->setAccessLicenseNumber($transport->getUpsApiKey())
            ->setShipperName($transport->getUpsShippingAccountName())
            ->setShipperNumber($transport->getUpsShippingAccountNumber())
            ->setShipperAddress($context->getShippingOrigin())
            ->setShipToAddress($context->getShippingAddress())
            ->setShipToName($context->getCustomer()->getFullName())
            ->setShipFromName($transport->getUpsShippingAccountName())
            ->setShipFromAddress($context->getShippingOrigin());
        
        if (null !== $shippingService) {
            $request->setServiceCode($shippingService->getCode())
                ->setServiceDescription($shippingService->getDescription());
        }

        $unitOfWeight = $transport->getUpsUnitOfWeight();
        if ($unitOfWeight === UPSSettings::UNIT_OF_WEIGHT_KGS) {
            $weightLimit = self::MAX_PACKAGE_WEIGHT_KGS;
        } else {
            $weightLimit = self::MAX_PACKAGE_WEIGHT_LBS;
        }

        $packages = $this->createPackages($context->getLineItems()->toArray(), $unitOfWeight, $weightLimit);
        if (count($packages) > 0) {
            $request->setPackages($packages);
            return $request;
        }
        return null;
    }

    /**
     * @param ShippingLineItemInterface[] $lineItems
     * @param string $unitOfWeight
     * @param int $weightLimit
     * @return Package[]|array
     * @throws \UnexpectedValueException
     */
    protected function createPackages($lineItems, $unitOfWeight, $weightLimit)
    {
        $packages = [];

        if (count($lineItems) === 0) {
            return $packages;
        }

        $productsParamsByUnit = $this->getProductsParamsByUnit($lineItems, $unitOfWeight);
        if (count($productsParamsByUnit) > 0) {
            /** @var array $productsParamsByWeightUnit */
            foreach ($productsParamsByUnit as $dimensionUnit => $productsParamsByWeightUnit) {
                $weight = 0;

                /** @var array $productsParams */
                foreach ($productsParamsByWeightUnit as $productsParams) {
                    if ($productsParams['weight'] > $weightLimit) {
                        return [];
                    }
                    if (($weight + $productsParams['weight']) > $weightLimit) {
                        $packages[] = Package::create(
                            (string)$unitOfWeight,
                            (string)$weight
                        );

                        $weight = 0;
                    }

                    $weight += $productsParams['weight'];
                }

                if ($weight > 0) {
                    $packages[] = Package::create(
                        (string)$unitOfWeight,
                        (string)$weight
                    );
                }
            }
        }

        return $packages;
    }

    /**
     * @param ShippingLineItemInterface[] $lineItems
     * @param string $upsWeightUnit
     *
     * @return array
     */
    protected function getProductsParamsByUnit(array $lineItems, $upsWeightUnit)
    {
        $productsParamsByUnit = [];

        foreach ($lineItems as $lineItem) {
            $upsWeight = $lineItem->getWeight();

            if (!$upsWeight) {
                return [];
            }

            for ($i = 0; $i < $lineItem->getQuantity(); $i++) {
                $productsParamsByUnit[$upsWeightUnit][] = [
                    'weightUnit' => $upsWeightUnit,
                    'weight' => $upsWeight
                ];
            }
        }

        return $productsParamsByUnit;
    }
}
