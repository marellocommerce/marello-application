<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
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
            $createOwnGroup = false;
            if ($form->has('createOwnGroup')) {
                $createOwnGroup = $form->get('createOwnGroup')->getData();
            }

            if ($form->isValid()) {
                $this->onSuccess($data, $typeBefore, $createOwnGroup);

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
     * @param bool $createOwnGroup
     */
    protected function onSuccess(Warehouse $entity, $typeBefore, $createOwnGroup = false)
    {
        $group = $entity->getGroup();
        $typeAfter = $entity->getWarehouseType();
        if ($typeAfter) {
            $typeAfter = $typeAfter->getName();
        }

        if ($typeBefore !== $typeAfter) {
            if ($typeBefore === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED) {
                $this->manager->remove($group);
                $entity->setGroup($this->getSystemWarehouseGroup());
            } elseif ($typeAfter === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED) {
                $label = $entity->getLabel();
                if ($group && !$group->isSystem() && $group->getWarehouses()->count() === 1) {
                    $group
                        ->setName($label)
                        ->setDescription(sprintf('%s group', $label));
                    $this->manager->persist($group);
                    $this->manager->flush();
                } else {
                    $group = $this->createOwnGroup($entity);
                }
            } elseif ($createOwnGroup) {
                $group = $this->createOwnGroup($entity);
            }
        } elseif ($createOwnGroup) {
            $group = $this->createOwnGroup($entity);
        }
        if ($group) {
            $entity->setGroup($group);
        }

        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * @param Warehouse $entity
     * @return WarehouseGroup
     */
    private function createOwnGroup(Warehouse $entity)
    {
        $label = $entity->getLabel();
        $group = new WarehouseGroup();
        $group
            ->setName($label)
            ->setOrganization($entity->getOwner())
            ->setDescription(sprintf('%s group', $label))
            ->setSystem(false);

        $this->manager->persist($group);
        $this->manager->flush();

        return $group;
    }

    /**
     * @return WarehouseGroup
     */
    private function getSystemWarehouseGroup()
    {
        return $this->manager->getRepository(WarehouseGroup::class)->findSystemWarehouseGroup();
    }
}
