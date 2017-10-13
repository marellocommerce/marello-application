<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadWarehouseTypeData;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class WarehouseHandler implements FormHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data, FormInterface $form, Request $request)
    {
        if (!$data instanceof Warehouse) {
            throw new \InvalidArgumentException('Argument data should be instance of Warehouse entity');
        }
        
        $typeBefore = $data->getWarehouseType();
        if ($typeBefore) {
            $typeBefore = $typeBefore->getName();
        }
        $form->setData($data);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->onSuccess($data, $typeBefore);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param Warehouse $entity
     * @param string $typeBefore
     */
    protected function onSuccess(Warehouse $entity, $typeBefore)
    {
        $typeAfter = $entity->getWarehouseType()->getName();

        if ($typeBefore !== $typeAfter) {
            if ($typeBefore === LoadWarehouseTypeData::FIXED_TYPE) {
                $this->manager->remove($entity->getGroup());
                $entity->setGroup($this->getSystemWarehouseGroup());
            } elseif ($typeAfter === LoadWarehouseTypeData::FIXED_TYPE) {
                $group = $entity->getGroup();
                $label = $entity->getLabel();
                if (!$group->isSystem() && $group->getWarehouses()->count() === 1) {
                    $group
                        ->setName($label)
                        ->setDescription(sprintf('%s group', $label));
                } else {
                    $group = new WarehouseGroup();
                    $group
                        ->setName($entity->getLabel())
                        ->setOrganization($entity->getOwner())
                        ->setDescription(sprintf('%s group', $entity->getLabel()))
                        ->setSystem(false);
                }
                $this->manager->persist($group);
                $this->manager->flush($group);
                $entity->setGroup($group);
            }
        }
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * @return WarehouseGroup
     */
    private function getSystemWarehouseGroup()
    {
        return $this->manager->getRepository(WarehouseGroup::class)->findOneBy(['system' => true]);
    }
}
