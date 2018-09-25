<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait HasEmailAddressTrait
{

    /**
     * @ORM\Column(type="text", nullable=false)
     *
     * @var string
     */
    protected $email;

    /**
     * Gets an email address which can be used to send messages
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getClass()
    {
        return Customer::class;
    }

    /**
     * Get names of fields contain email addresses
     *
     * @return string[]|null
     */
    public function getEmailFields()
    {
        return null;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }
}
