<?php

namespace Marello\Bundle\CoreBundle\Model;

interface AdditionalPlaceholderDataInterface
{
    /** @return string */
    public function getName();

    /** @return string */
    public function getLabel();

    /** @return string */
    public function getPlaceholder();

    /** @return array */
    public function getPlaceHolderSections();
}
