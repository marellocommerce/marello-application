<?php

namespace Marello\Bundle\OroCommerceBundle\Client\Guzzle\Http\Message;

use Guzzle\Http\Message\Request as BaseRequest;
use Marello\Bundle\OroCommerceBundle\Client\Guzzle\Http\Url;

class Request extends BaseRequest
{
    /**
     * {@inheritdoc}
     */
    public function setUrl($url)
    {
        if ($url instanceof Url) {
            $this->url = $url;
        } else {
            $this->url = Url::factory($url);
        }

        // Update the port and host header
        $this->setPort($this->url->getPort());

        if ($this->url->getUsername() || $this->url->getPassword()) {
            $this->setAuth($this->url->getUsername(), $this->url->getPassword());
            // Remove the auth info from the URL
            $this->url->setUsername(null);
            $this->url->setPassword(null);
        }

        return $this;
    }
}
