<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\CustomerEmail;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;

class CustomerEmailOwnerProvider implements EmailOwnerProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function getEmailOwnerClass()
    {
        return Customer::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner(EntityManager $em, $email)
    {
        return $em
            ->getRepository(Customer::class)
            ->findOneBy(compact('email'));
    }
}
