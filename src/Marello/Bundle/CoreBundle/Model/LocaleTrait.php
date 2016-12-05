<?php

namespace Marello\Bundle\CoreBundle\Model;

trait LocaleTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=32, nullable=true)
     */
    protected $locale;

    /**
     * @return String
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}