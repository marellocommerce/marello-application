<?php

namespace Marello\Bundle\CustomerBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;

class CustomerEmailOwnerProvider implements EmailOwnerProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function getEmailOwnerClass(): string
    {
        return Customer::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner(EntityManagerInterface $em, string $email): ?EmailOwnerInterface
    {
        return $em
            ->getRepository(Customer::class)
            ->findOneBy(compact('email'));
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizations(EntityManagerInterface $em, string $email): array
    {
        $rows = $em->createQueryBuilder()
            ->from(Customer::class, 'c')
            ->select('IDENTITY(c.organization) AS id')
            ->where('c.email = :email')
            ->setParameter('email', mb_strtolower($email))
            ->getQuery()
            ->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            $result[] = (int)$row['id'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmails(EntityManagerInterface $em, int $organizationId): iterable
    {
        $qb = $em->createQueryBuilder()
            ->from(Customer::class, 'c')
            ->select('c.email')
            ->where('c.organization = :organizationId')
            ->setParameter('organizationId', $organizationId)
            ->orderBy('c.id');
        $iterator = new BufferedQueryResultIterator($qb);
        $iterator->setBufferSize(5000);
        foreach ($iterator as $row) {
            yield $row['email'];
        }
    }
}
