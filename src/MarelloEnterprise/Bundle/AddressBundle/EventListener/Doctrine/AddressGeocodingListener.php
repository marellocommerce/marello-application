<?php

namespace MarelloEnterprise\Bundle\AddressBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\Factory\GoogleApiContextFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Provider\GoogleApiResultsProviderInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GeocodingApiResultFactory;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class AddressGeocodingListener implements FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;
    
    const GEOCODE_SOURCE_FIELDS = ['street', 'street2', 'city', 'postalCode', 'region', 'country'];

    /**
     * @var GoogleApiResultsProviderInterface
     */
    protected $geocodingApiResultsProvider;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param GoogleApiResultsProviderInterface $geocodingApiResultsProvider
     * @param Session $session
     */
    public function __construct(
        GoogleApiResultsProviderInterface $geocodingApiResultsProvider,
        Session $session
    ) {
        $this->geocodingApiResultsProvider = $geocodingApiResultsProvider;
        $this->session = $session;
    }

    /**
     * @param MarelloAddress $address
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(MarelloAddress $address, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $changes = $em->getUnitOfWork()->getEntityChangeSet($address);
        if (count(array_intersect(array_keys($changes), self::GEOCODE_SOURCE_FIELDS))) {
            $this->geocodeAddress($address, $em);
        }
    }

    /**
     * @param MarelloAddress $address
     * @param EntityManager $em
     */
    private function geocodeAddress(MarelloAddress $address, EntityManager $em)
    {
        if ($this->featureChecker->isFeatureEnabled('address_geocoding')) {
            $results = $this->geocodingApiResultsProvider
                ->getApiResults(GoogleApiContextFactory::createContext($address));

            $eeAddress = $em->getRepository(MarelloEnterpriseAddress::class)->findOneBy(['address' => $address]) ?:
                new MarelloEnterpriseAddress();
            $eeAddress->setAddress($address);

            if ($results->getStatus() === false) {
                $eeAddress
                    ->setLatitude(null)
                    ->setLongitude(null);
                $this->session->getFlashBag()->add(
                    'warning',
                    $results->getErrorMessage()
                );
            } else {
                $eeAddress
                    ->setLatitude($results->getResult()[GeocodingApiResultFactory::LATITUDE])
                    ->setLongitude($results->getResult()[GeocodingApiResultFactory::LONGITUDE]);
            }
            $em->persist($eeAddress);
            $em->flush();
        }
    }
}
