<?php

namespace Marello\Bundle\MageBridgeBundle\ActionHandler;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Marello\Bundle\MageBridgeBundle\Entity\MagentoRestTransport as Transport;
use Oro\Bundle\IntegrationBundle\Manager\FieldsChangesManager;
use Symfony\Component\HttpFoundation\Session\Session;

class TransportTokenAuthActionHandler implements TransportActionHandlerInterface
{
    /** @var array */
    protected $tokens = [];

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return mixed
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param mixed $tokens
     */
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;

        return $this;
    }

    /**
     * @var FieldsChangesManager
     */
    protected $fieldsChangesManager;

    /**
     * @param FieldsChangesManager $fieldsChangesManager
     */
    public function setFieldsChangesManager(FieldsChangesManager $fieldsChangesManager)
    {
        $this->fieldsChangesManager = $fieldsChangesManager;
    }

    /**
     * {@inheritdoc}
     */
    public function handleAction(Transport $transport)
    {
        $this->entityManager->getConnection()->beginTransaction();
        $this->entityManager->getConnection()->setAutoCommit(false);

        $entityClassName = get_class($transport);

        $this->entityManager->getConnection()->executeQuery(
            sprintf(
                'UPDATE %s SET token_key="%s", token_secret="%s" WHERE id=%s',
                $this->entityManager->getClassMetadata($entityClassName)->getTableName(),
                $this->tokens['oauth_token'],
                $this->tokens['oauth_token_secret'],
                $transport->getId()
            )
        );

        $this->entityManager->commit();
        $this->entityManager->flush();

        return true;
    }
}
