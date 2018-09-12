<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Command;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\Factory\GoogleApiContextFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GeocodingApiResultFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddressGeocodingCommand extends ContainerAwareCommand
{
    /**
     * Command name
     */
    const COMMAND_NAME = 'marello:address-geocoding';

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
        $featureChecker = $this->getContainer()->get('oro_featuretoggle.checker.feature_checker');
        if (!$featureChecker->isFeatureEnabled('address_geocoding')) {
            $output->writeln('The address geocoding feature is disabled. The command will not run.');

            return 0;
        }
        $doctrineHelper = $this->getContainer()->get('oro_entity.doctrine_helper');
        $geocodingApiResultsProvider = $this->getContainer()
            ->get('marelloenterprise.google_api.result_provider.geocoding');
        $em = $doctrineHelper->getEntityManagerForClass(MarelloAddress::class);
        $addresses = $em
            ->getRepository(MarelloAddress::class)
            ->findAll();
        $eeAddresses = $doctrineHelper
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
            $results = $geocodingApiResultsProvider
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
