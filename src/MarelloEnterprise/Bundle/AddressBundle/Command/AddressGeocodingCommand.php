<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Command;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\Factory\GoogleApiContextFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Provider\GoogleApiResultsProviderInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GeocodingApiResultFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddressGeocodingCommand extends Command
{
    /**
     * Command name
     */
    const COMMAND_NAME = 'marello:address-geocoding';

    /**
     * @var FeatureChecker
     */
    private $featureChecker;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var GoogleApiResultsProviderInterface
     */
    private $geocodingApiResultsProvider;

    /**
     * @param FeatureChecker $featureChecker
     * @param DoctrineHelper $doctrineHelper
     * @param GoogleApiResultsProviderInterface $geocodingApiResultsProvider
     */
    public function __construct(
        FeatureChecker $featureChecker,
        DoctrineHelper $doctrineHelper,
        GoogleApiResultsProviderInterface $geocodingApiResultsProvider
    ) {
        $this->featureChecker = $featureChecker;
        $this->doctrineHelper = $doctrineHelper;
        $this->geocodingApiResultsProvider = $geocodingApiResultsProvider;

        parent::__construct();
    }

    /**
     * {@internaldoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Collecting addresses coordinates via Google Maps Geocoding API');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->featureChecker->isFeatureEnabled('address_geocoding')) {
            $output->writeln('The address geocoding feature is disabled. The command will not run.');

            return 0;
        }
        $em = $this->doctrineHelper->getEntityManagerForClass(MarelloAddress::class);
        $addresses = $em
            ->getRepository(MarelloAddress::class)
            ->findAll();
        $eeAddresses = $this->doctrineHelper
            ->getEntityManagerForClass(MarelloEnterpriseAddress::class)
            ->getRepository(MarelloEnterpriseAddress::class)
            ->findAll();
        $geocodedAddresses = array_map(function (MarelloEnterpriseAddress $eeAddress) {
            return $eeAddress->getAddress();
        }, $eeAddresses);

        $successGeocodingCnt = 0;
        $notSuccessGeocodingCnt = 0;

        foreach ($geocodedAddresses as $ga) {
            array_splice($addresses, array_search($ga, $addresses), 1);
        }
        foreach ($addresses as $address) {
            $results = $this->geocodingApiResultsProvider
                ->getApiResults(GoogleApiContextFactory::createContext($address));
            if (!$results) {
                continue;
            }
            if ($results->getStatus() === false && $results->getErrorType() === GoogleApiResult::ERROR_TYPE) {
                $em->flush();
                throw new \Exception($results->getErrorMessage(), 1);
            } else {
                $eeAddress = new MarelloEnterpriseAddress();
                $eeAddress->setAddress($address);
                if ($results->getStatus() === false && $results->getErrorType() === GoogleApiResult::WARNING_TYPE) {
                    $notSuccessGeocodingCnt++;
                } else {
                    $eeAddress
                        ->setLatitude($results->getResult()[GeocodingApiResultFactory::LATITUDE])
                        ->setLongitude($results->getResult()[GeocodingApiResultFactory::LONGITUDE]);
                    $successGeocodingCnt++;
                }
                $em->persist($eeAddress);
            }
            $em->flush();
        }
        
        if ($notSuccessGeocodingCnt > 0) {
            $message = (
                    sprintf(
                        'Google Maps Geocoding API found coordinates for %d addresses,
                         but for %d addresses coordinates were not found',
                        $successGeocodingCnt,
                        $notSuccessGeocodingCnt
                    )
                );
        } else {
            $message = (
                    sprintf(
                        'Google Maps Geocoding API found coordinates for %d addresses',
                        $successGeocodingCnt
                    )
                );
        }
        $output->writeln($message);

        return 0;
    }
}
