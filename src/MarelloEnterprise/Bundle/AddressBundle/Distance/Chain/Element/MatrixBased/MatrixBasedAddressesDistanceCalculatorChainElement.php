<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\MatrixBased;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\AbstractAddressesDistanceCalculatorChainElement as
    AbstractAddressesDistanceCalculator;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\Factory\GoogleApiContextFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Exception\GoogleApiException;
use MarelloEnterprise\Bundle\GoogleApiBundle\Provider\GoogleApiResultsProviderInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\DistanceMatrixApiResultFactory;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

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
     * @var Session
     */
    protected $session;

    /**
     * @param GoogleApiResultsProviderInterface $distanceMatrixResultsProvider
     * @param LoggerInterface $logger
     * @param Session $session
     */
    public function __construct(
        GoogleApiResultsProviderInterface $distanceMatrixResultsProvider,
        LoggerInterface $logger,
        Session $session
    ) {
        $this->distanceMatrixResultsProvider = $distanceMatrixResultsProvider;
        $this->logger = $logger;
        $this->session = $session;
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
            try {
                $results = $this->distanceMatrixResultsProvider->getApiResults(
                    GoogleApiContextFactory::createContext($originAddress, $destinationAddress)
                );
                if ($results->getStatus() === true && $results->getResult()) {
                    return $results->getResult()[DistanceMatrixApiResultFactory::DISTANCE] / 1000;
                } elseif ($results->getErrorMessage() && $results->getErrorCode()) {
                    $this->logger->error(sprintf('%s: %s', $results->getErrorCode(), $results->getErrorMessage()));
                }
            } catch (GoogleApiException $e) {
                $this->session->getFlashBag()->add('warning', $e->getMessage());
            }
        }
        
        return null;
    }
}
