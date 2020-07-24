<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

class CustomerNormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    const CUSTOMER_ID = 'orocommerce_customer_id';

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return ($data instanceof Company && isset($context['channel']) &&
            $this->getIntegrationChannel($context['channel']));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return isset($data['type']) && $data['type'] === 'customers' && ($type == Company::class);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return $this->createCompany($data);
    }

    /**
     * @param array $data
     * @return Company
     */
    public function createCompany(array $data)
    {
        $parentData = $this->getProperty($data, 'parent');
        $addresses = $this->getProperty($data, 'addresses');
        $paymentTermData = $this->getProperty($data, 'paymentTerm');
        $company = new Company();
        $company->setName($this->getProperty($data, 'name'));
        $company->setOrocommerceOriginId($this->getProperty($data, 'id'));
        if ($parentData['id']) {
            $parent = new Company();
            $parent->setName($this->getProperty($parentData, 'name'));
            $company->setParent($parent);
        }
        if ($paymentTermData['id']) {
            $company->setPaymentTerm($this->preparePaymentTerm($paymentTermData));
        }
        foreach ($addresses as $address) {
            if ($companyAddress = $this->prepareAddress($address)) {
                $company->addAddress($companyAddress);
            }
        }
        
        return $company;
    }

    /**
     * @param array $data
     * @return PaymentTerm
     */
    private function preparePaymentTerm(array $data)
    {
        $label = $this->getProperty($data, 'label');
        preg_match_all('/[0-9]+/', $label, $matches);

        $paymentTerm = new PaymentTerm();
        $paymentTerm
            ->addLabel((new LocalizedFallbackValue())->setString($label))
            ->setCode(str_replace(' ', '_', $label))
            ->setTerm(!empty($matches) && !empty($matches[0]) ? reset($matches[0]) : 30);

        return $paymentTerm;
    }
    
    /**
     * @param array $data
     * @return MarelloAddress
     */
    public function prepareAddress(array $data)
    {
        if (isset($data['type']) && 'customeraddresses' === $data['type']) {
            $countryCode = $this->getProperty($data, 'country')['id'];
            $regionCode = $this->getProperty($data, 'region')['id'];
            if (!$countryCode && !$regionCode) {
                return null;
            }
            $country = $this->registry
                ->getManagerForClass(Country::class)
                ->getRepository(Country::class)
                ->find($countryCode);
            $region = $this->registry
                ->getManagerForClass(Region::class)
                ->getRepository(Region::class)
                ->find($regionCode);
            if ($country && $region) {
                $address = new MarelloAddress();
                $address
                    ->setCountry($country)
                    ->setRegion($region);
                if ($firstName = $this->getProperty($data, 'firstName')) {
                    $address->setFirstName($firstName);
                }
                if ($lastName = $this->getProperty($data, 'lastName')) {
                    $address->setLastName($lastName);
                }
                if ($middleName = $this->getProperty($data, 'middleName')) {
                    $address->setMiddleName($middleName);
                }
                if ($namePrefix = $this->getProperty($data, 'namePrefix')) {
                    $address->setNamePrefix($namePrefix);
                }
                if ($nameSuffix = $this->getProperty($data, 'nameSuffix')) {
                    $address->setNameSuffix($nameSuffix);
                }
                if ($city = $this->getProperty($data, 'city')) {
                    $address->setCity($city);
                }
                if ($postalCode = $this->getProperty($data, 'postalCode')) {
                    $address->setPostalCode($postalCode);
                }
                if ($street = $this->getProperty($data, 'street')) {
                    $address->setStreet($street);
                }
                if ($street2 = $this->getProperty($data, 'street2')) {
                    $address->setStreet2($street2);
                }
                if ($phone = $this->getProperty($data, 'phone')) {
                    $address->setPhone($phone);
                }
                if ($company = $this->getProperty($data, 'organization')) {
                    $address->setCompany($company);
                }
                return $address;
            }
        }

        return null;
    }
}
