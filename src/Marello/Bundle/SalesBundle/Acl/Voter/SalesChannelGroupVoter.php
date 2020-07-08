<?php

namespace Marello\Bundle\SalesBundle\Acl\Voter;

use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelGroupRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;

/**
 * Disables deleting the sales channel group in case when it has attached integration
 */
class SalesChannelGroupVoter extends AbstractEntityVoter
{
    /** @var array */
    protected $supportedAttributes = [BasicPermission::DELETE];

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        /** @var SalesChannelGroupRepository $scgRepository */
        $scgRepository = $this->doctrineHelper->getEntityRepository(SalesChannelGroup::class);

        if ($scgRepository->hasAttachedIntegration($identifier)) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }
}
