<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_order_customer_email")
 */
class CustomerEmail implements EmailInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    protected $email;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\Customer", inversedBy="emails")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Customer
     */
    protected $emailOwner;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
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

    /**
     * @return Customer
     */
    public function getEmailOwner()
    {
        return $this->emailOwner;
    }

    /**
     * @param Customer $emailOwner
     *
     * @return $this
     */
    public function setEmailOwner($emailOwner)
    {
        $this->emailOwner = $emailOwner;

        return $this;
    }

    /**
     * Get name of field contains an email address
     *
     * @return string
     */
    public function getEmailField()
    {
        return 'email';
    }

    /**
     * Get entity unique id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
