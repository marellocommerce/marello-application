<?php

namespace Marello\Bundle\MageBridgeBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner as BaseGenericOAuth1ResourceOwner;

class MagentoResourceOwner extends BaseGenericOAuth1ResourceOwner
{
    use ConfigurableCredentialsTrait;
}
