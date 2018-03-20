<?php

namespace Marello\Bundle\MageBridgeBundle\ActionHandler;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Marello\Bundle\MageBridgeBundle\Entity\MagentoRestTransport as Transport;
use Oro\Bundle\IntegrationBundle\Manager\FieldsChangesManager;
use Symfony\Component\HttpFoundation\Session\Session;

class TransportTokenAuthActionHandler implements TransportActionHandlerInterface
{
    protected $tokens;

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
     * @var EntityManagerInterface
     */
    private $entityManager;

    private $session;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, Session $session, FieldsChangesManager $fieldsChangesManager)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->fieldsChangesManager = $fieldsChangesManager;
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

        $transport->setTokenKey($this->session->get('oauth_token'))
            ->setTokenSecret($this->session->get('oauth_token_secret'));

        $entityClassName = get_class($transport);

        $this->entityManager->getConnection()->executeQuery(
            sprintf(
                'UPDATE %s SET token_key="%s", token_secret="%s" WHERE id=%s',
                $this->entityManager->getClassMetadata($entityClassName)->getTableName(),
                $this->session->get('oauth_token'),
                $this->session->get('oauth_token_secret'),
                $transport->getId()
            )
        );

        $this->entityManager->commit();
        $this->entityManager->flush();

        //DEBUG:
        file_put_contents("/var/www/app/logs/transport.log", print_r($transport, true), FILE_APPEND);

        return true;
    }

//    public function redirectUrl($url)
//    {
//            header("Access-Control-Allow-Origin: {$url}");
//            header("Location: {$url}");
//            exit;
//    }
}
