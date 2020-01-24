<?php

namespace Marello\Bundle\LocaleBundle\Model;

interface LocaleAwareInterface
{
    public function getLocale();

    public function setLocale($locale);
}
