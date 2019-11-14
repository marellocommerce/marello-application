<?php

namespace Marello\Bundle\ProductBundle\Model;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

/**
 * @method LocalizedFallbackValue getName(Localization $localization = null)
 * @method LocalizedFallbackValue getDefaultName()
 * @method setDefaultName(string $value)
 */
class ExtendProduct
{
    public function __construct()
    {
    }
}
