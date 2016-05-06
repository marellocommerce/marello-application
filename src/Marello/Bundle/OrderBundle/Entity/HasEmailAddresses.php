<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait HasEmailAddresses
{
    /**
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\OrderBundle\Entity\CustomerEmail",
     *     mappedBy="emailOwner",
     *     cascade={"persist", "remove"}
     * )
     *
     * @var Collection|CustomerEmail[]
     */
    protected $emails;

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
        return ['emails'];
    }

    /**
     * @return Collection|CustomerEmail[]
     */
    public function getEmails()
    {
        return $this->emails;
    }
}
