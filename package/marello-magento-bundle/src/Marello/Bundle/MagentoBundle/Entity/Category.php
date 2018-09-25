<?php

namespace Marello\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Marello\Bundle\MagentoBundle\Model\ExtendCategory;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * Class Category
 *
 * @package Marello\Bundle\MarelloMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(
 *      name="marello_magento_category",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="unq_site_idx",
 *              columns={
 *                  "category_code",
 *                  "origin_id",
 *                  "channel_id"
 *              }
 *         )
 *      },
 *      indexes={
 *          @ORM\Index(name="marello_magento_category_name_idx",columns={"category_name"})
 *      }
 * )
 * @Config(
 *      defaultValues={
 *          "note"={
 *              "immutable"=true
 *          },
 *          "activity"={
 *              "immutable"=true
 *          },
 *          "attachment"={
 *              "immutable"=true
 *          }
 *      }
 * )
 */
class Category extends ExtendCategory implements OriginAwareInterface, IntegrationAwareInterface
{
    use IntegrationEntityTrait, OriginTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="category_code", type="string", length=32, nullable=true)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="category_name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }
}
