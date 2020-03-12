<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy;

class PaymentTermImportStrategy extends AbstractImportStrategy
{
    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {

        if ($entity instanceof PaymentTerm) {
            $paymentTerm = $this->processPaymentTerm($entity);

            return $this->validateAndUpdateContext($paymentTerm);
        }
        
        return null;
    }

    /**
     * @param PaymentTerm $entity
     * @return PaymentTerm
     */
    public function processPaymentTerm(PaymentTerm $entity)
    {
        $criteria = [
            'code' => $entity->getCode()
        ];
        $paymentTerm = $this->getEntityByCriteria($criteria, $entity);
        if ($paymentTerm) {
            $this->strategyHelper->importEntity(
                $paymentTerm,
                $entity,
                ['id']
            );
        } else {
            $paymentTerm = $entity;
        }

        return $paymentTerm;
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
