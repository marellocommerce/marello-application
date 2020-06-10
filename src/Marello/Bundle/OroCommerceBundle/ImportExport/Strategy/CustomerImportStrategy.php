<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy;

class CustomerImportStrategy extends AbstractImportStrategy
{
    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {

        if ($entity instanceof Company) {
            $company = $this->processCompany($entity);

            return $this->validateAndUpdateContext($company);
        }
        
        return null;
    }

    /**
     * @param Company $entity
     * @return Company
     */
    public function processCompany(Company $entity)
    {
        $channel = $this->context->getValue('channel');
        $organization = $channel->getOrganization();
        $criteria = [
            'name' => $entity->getName()
        ];
        $company = $this->getEntityByCriteria($criteria, $entity);
        if ($company) {
            $newAddresses = $entity->getAddresses()->toArray();
            $existingAddresses = $company->getAddresses()->toArray();
            foreach ($existingAddresses as $existingAddress) {
                $company->removeAddress($existingAddress);
            }
            $this->strategyHelper->importEntity(
                $company,
                $entity,
                ['id', 'createdAt', 'parent', 'addresses', 'paymentTerm', 'organization']
            );
            foreach ($newAddresses as $newAddress) {
                $matched = false;
                foreach ($existingAddresses as $existingAddress) {
                    if ((string)$newAddress === (string)$existingAddress) {
                        $company->addAddress($existingAddress);
                        $matched = true;
                        break;
                    }
                }
                if ($matched === false) {
                    $company->addAddress($newAddress);
                }
            }
        } else {
            $company = $entity;
            $company->setOrganization($organization);
        }
        $parent = $entity->getParent();
        if ($parent && !$parent->getId()) {
            $criteria = [
                'name' => $parent->getName()
            ];
            /** @var Company $existingParent */
            $existingParent = $this->getEntityByCriteria($criteria, $parent);
            if ($existingParent) {
                $company->setParent($existingParent);
            } else {
                $parent->setOrganization($organization);
                $company->setParent($parent);
            }
        }
        $paymentTerm = $entity->getPaymentTerm();
        if ($paymentTerm && !$paymentTerm->getId()) {
            $criteria = [
                'code' => $paymentTerm->getCode()
            ];
            /** @var PaymentTerm $existingPaymentTerm */
            $existingPaymentTerm = $this->getEntityByCriteria($criteria, $paymentTerm);
            if ($existingPaymentTerm) {
                $company->setPaymentTerm($existingPaymentTerm);
            } else {
                $company->setPaymentTerm($paymentTerm);
            }
        }

        return $company;
    }

    /**
     * @param MarelloAddress $entity
     * @return MarelloAddress
     */
    public function processAddress(MarelloAddress $entity)
    {
        $criteria = [
            'firstName' => $entity->getFirstName(),
            'lastName' => $entity->getLastName(),
            'postalCode' => $entity->getPostalCode(),
            'city' => $entity->getCity(),
            'street' => $entity->getStreet(),
        ];
        $address = $this->getEntityByCriteria($criteria, MarelloAddress::class);
        if ($address) {
            if ($entity->getOrganization()) {
                $address->setCompany($entity->getOrganization());
            }
            if ($entity->getCountry()) {
                $address->setCountry($entity->getCountry());
            }
            if ($entity->getRegion()) {
                $address->setRegion($entity->getRegion());
            }
            if ($entity->getPostalCode() && strlen($entity->getPostalCode()) > 0) {
                $address->setPostalCode($entity->getPostalCode());
            }
            if ($entity->getCity() && strlen($entity->getCity()) > 0) {
                $address->setCity($entity->getCity());
            }
            if ($entity->getStreet() && strlen($entity->getStreet()) > 0) {
                $address->setStreet($entity->getStreet());
            }
            if ($entity->getStreet2() && strlen($entity->getStreet2()) > 0) {
                $address->setStreet2($entity->getStreet2());
            }
            if ($entity->getPhone() && strlen($entity->getPhone()) > 0) {
                $address->setPhone($entity->getPhone());
            }
        } else {
            $address = $entity;
        }

        $em = $this->strategyHelper->getEntityManager(MarelloAddress::class);
        $em->persist($address);
        $em->flush($address);

        return $address;
    }
    
    /**
     * @param ContextInterface $context
     */
    public function setImportExportContext(ContextInterface $context)
    {
        $this->context = $context;
    }
    
    /**
     * @param array         $criteria
     * @param object|string $entity object to get class from or class name
     *
     * @return object
     */
    private function getEntityByCriteria(array $criteria, $entity)
    {
        if (is_object($entity)) {
            $entityClass = ClassUtils::getClass($entity);
        } else {
            $entityClass = $entity;
        }
        return $this->getEntityRepository($entityClass)->findOneBy($criteria);
    }
    
    /**
     * @param string $entityName
     *
     * @return EntityRepository
     */
    private function getEntityRepository($entityName)
    {
        return $this->strategyHelper->getEntityManager($entityName)->getRepository($entityName);
    }
    
    /**
     * @param object $entity
     *
     * @return null|object
     */
    private function validateAndUpdateContext($entity)
    {
        // validate entity
        $validationErrors = $this->strategyHelper->validateEntity($entity);
        if ($validationErrors) {
            $this->context->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->context);
            return null;
        }
        // increment context counter
        if ($entity->getId()) {
            $this->context->incrementUpdateCount();
        } else {
            $this->context->incrementAddCount();
        }
        return $entity;
    }
}
