<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait HasFullNameTrait
{
    /**
     * @ORM\Column(name="name_prefix", type="string", nullable=true)
     *
     * @var string
     */
    protected $namePrefix;

    /**
     * @ORM\Column(name="first_name", type="string", nullable=false)
     *
     * @var string
     */
    protected $firstName;

    /**
     * @ORM\Column(name="middle_name", type="string", nullable=true)
     *
     * @var string
     */
    protected $middleName;

    /**
     * @ORM\Column(name="last_name", type="string", nullable=false)
     *
     * @var string
     */
    protected $lastName;

    /**
     * @ORM\Column(name="name_suffix", type="string", nullable=true)
     *
     * @var string
     */
    protected $nameSuffix;

    /**
     * @return string
     */
    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    /**
     * @param string $namePrefix
     *
     * @return $this
     */
    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @param mixed $middleName
     *
     * @return $this
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     *
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNameSuffix()
    {
        return $this->nameSuffix;
    }

    /**
     * @param mixed $nameSuffix
     *
     * @return $this
     */
    public function setNameSuffix($nameSuffix)
    {
        $this->nameSuffix = $nameSuffix;

        return $this;
    }

    /**
     * Returns all names concatenated into full name.
     *
     * @return string
     */
    public function getFullName()
    {
        $names = array_filter([
            $this->namePrefix,
            $this->firstName,
            $this->middleName,
            $this->lastName,
            $this->nameSuffix,
        ]);

        return implode(' ', $names);
    }
}
