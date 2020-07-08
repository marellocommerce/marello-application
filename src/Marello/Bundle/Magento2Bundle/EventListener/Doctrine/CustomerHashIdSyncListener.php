<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\Magento2Bundle\Entity\Customer as MagentoCustomer;
use Marello\Bundle\Magento2Bundle\Entity\Repository\CustomerRepository;
use Marello\Bundle\Magento2Bundle\Generator\CustomerHashIdGeneratorInterface;

class CustomerHashIdSyncListener
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CustomerHashIdGeneratorInterface
     */
    protected $hashIdGenerator;

    /**
     * @param Registry $registry
     * @param CustomerHashIdGeneratorInterface $hashIdGenerator
     */
    public function __construct(Registry $registry, CustomerHashIdGeneratorInterface $hashIdGenerator)
    {
        $this->registry = $registry;
        $this->hashIdGenerator = $hashIdGenerator;
    }

    /**
     * @param MagentoCustomer $magentoCustomer
     */
    public function prePersist(MagentoCustomer $magentoCustomer)
    {
        $hashId = $this->hashIdGenerator->generateHashId($magentoCustomer->getInnerCustomer());
        $magentoCustomer->setHashId($hashId);
    }

    /**
     * @param Customer $customer
     */
    public function postUpdate(Customer $customer)
    {
        $this->updateMagentoCustomersHashId($customer);
    }

    /**
     * @param Customer $customer
     * @return mixed
     */
    protected function updateMagentoCustomersHashId(Customer $customer)
    {
        $hashId = $this->hashIdGenerator->generateHashId($customer);
        /** @var CustomerRepository $repository */
        $repository = $this->registry
            ->getManagerForClass(MagentoCustomer::class)
            ->getRepository(MagentoCustomer::class);

        return $repository->updateHashIdByInnerCustomer($hashId, $customer);
    }
}
