<?php

namespace Marello\Bundle\CoreBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerTrait;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressVisibilityManager;

/**
 * Updates email address visibility once the EmailHolders are flushed (Marello:Customer && Oro:User)
 * bug(?) prevents email activities being shown on entities when created by (new) Customer and User.
 */
class EmailAddressVisibilityListener implements OptionalListenerInterface
{
    use OptionalListenerTrait;

    /** @var EmailAddressVisibilityManager $emailAddressVisibilityManager */
    private $emailAddressVisibilityManager;

    public function __construct(EmailAddressVisibilityManager $emailAddressVisibilityManager)
    {
        $this->emailAddressVisibilityManager = $emailAddressVisibilityManager;
    }

    /**
     * @param OnFlushEventArgs $args
     * @return void
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        if (!$this->enabled) {
            return;
        }

        $entityManager = $args->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $newEntities = $unitOfWork->getScheduledEntityInsertions();
        $entities = array_merge($unitOfWork->getScheduledEntityUpdates(), $newEntities);
        foreach ($entities as $entity) {
            if ($entity instanceof EmailHolderInterface) {
                $this->updateEmailAddressVisibilities($entity);
            }
        }
    }

    /**
     * @param EmailHolderInterface $entity
     * @return void
     */
    private function updateEmailAddressVisibilities(EmailHolderInterface $entity): void
    {
        if ($entity instanceof OrganizationAwareInterface) {
            if ($entity->getOrganization()) {
                $this->emailAddressVisibilityManager
                    ->updateEmailAddressVisibility(
                        $entity->getEmail(),
                        $entity->getOrganization()->getId(),
                        true
                    );
            }
        }

        if (method_exists($entity, 'getOrganizations')) {
            foreach ($entity->getOrganizations(true) as $organization) {
                if ($organization->getId()) {
                    $this->emailAddressVisibilityManager
                        ->updateEmailAddressVisibility(
                            $entity->getEmail(),
                            $organization->getId(),
                            true
                        );
                }
            }
        }
    }
}
