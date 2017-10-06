<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\MatrixBased;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\AbstractAddressesDistanceCalculatorChainElement as
    AbstractAddressesDistanceCalculator;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\Factory\GoogleApiContextFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Provider\GoogleApiResultsProviderInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\DistanceMatrixApiResultFactory;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;

class MatrixBasedAddressesDistanceCalculatorChainElement extends AbstractAddressesDistanceCalculator implements
    FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;
    
    /**
     * @var GoogleApiResultsProviderInterface
     */
    private $distanceMatrixResultsProvider;

    /**
     * @param GoogleApiResultsProviderInterface $distanceMatrixResultsProvider
     */
    public function __construct(
        GoogleApiResultsProviderInterface $distanceMatrixResultsProvider
    ) {
        $this->distanceMatrixResultsProvider = $distanceMatrixResultsProvider;
    }

    protected function getDistance(
        MarelloAddress $originAddress,
        MarelloAddress $destinationAddress,
        $unit = 'metric'
    ) {
        if ($this->featureChecker->isFeatureEnabled('address_distance_calculation_matrix_based')) {
            $results = $this->distanceMatrixResultsProvider->getApiResults(
                GoogleApiContextFactory::createContext($originAddress, $destinationAddress)
            );
            if ($results->getStatus() === true && $results->getResult()) {
                return $results->getResult()[DistanceMatrixApiResultFactory::DISTANCE]/1000;
            } else {
                throw new \Exception($results->getErrorMessage(), $results->getErrorCode());
            }
        }
        
        return null;
    }
}
