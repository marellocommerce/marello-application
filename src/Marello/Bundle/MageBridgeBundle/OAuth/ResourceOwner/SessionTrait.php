<?php

namespace Marello\Bundle\MageBridgeBundle\OAuth\ResourceOwner;

use Symfony\Component\HttpFoundation\Session\Session;

trait SessionTrait
{
    protected $session;

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Session $session
     * @return $this
     */
    public function setSession(Session $session)
    {
        $this->session = $session;

        return $this;
    }
}
