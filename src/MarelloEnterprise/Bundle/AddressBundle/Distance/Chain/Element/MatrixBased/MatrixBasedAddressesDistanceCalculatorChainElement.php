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
use Psr\Log\LoggerInterface;

class MatrixBasedAddressesDistanceCalculatorChainElement extends AbstractAddressesDistanceCalculator implements
    FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;
    
    /**
     * @var GoogleApiResultsProviderInterface
     */
    private $distanceMatrixResultsProvider;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param GoogleApiResultsProviderInterface $distanceMatrixResultsProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        GoogleApiResultsProviderInterface $distanceMatrixResultsProvider,
        LoggerInterface $logger
    ) {
        $this->distanceMatrixResultsProvider = $distanceMatrixResultsProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
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
            } elseif ($results->getErrorMessage() && $results->getErrorCode()) {
                $this->logger->error(sprintf('%s: %s', $results->getErrorCode(), $results->getErrorMessage()));
            }
        }
        
        return null;
    }
}
