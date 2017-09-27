<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class WarehouseGroupHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, ObjectManager $manager)
    {
        $this->form = $form;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param WarehouseGroup $entity
     * @param Request $request
     * @return bool
     */
    public function process(WarehouseGroup $entity, Request $request)
    {
        $warehousesBefore = $entity->getWarehouses()->toArray();
        $this->form->setData($entity);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity, $warehousesBefore);

                return true;
            }
        }

        return false;
    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    public function getFormView()
    {
        $config = $this->form->getConfig();

        /** @var FormInterface $form */
        $form = $config->getFormFactory()->createNamed(
            $this->form->getName(),
            $config->getType()->getName(),
            $this->form->getData()
        );

        return $form->createView();
    }

    /**
     * "Success" form handler
     *
     * @param WarehouseGroup $entity
     * @param Warehouse[] $warehousesBefore
     */
    protected function onSuccess(WarehouseGroup $entity, $warehousesBefore)
    {
        $warehousesAfter = $entity->getWarehouses()->toArray();
        $diff = array_filter($warehousesBefore, function ($entity) use ($warehousesAfter) {
            return !in_array($entity, $warehousesAfter);
        });

        $systemGroup = $this->getSystemWarehouseGroup();
        /** @var Warehouse $warehouse */
        foreach ($diff as $warehouse) {
            $warehouse->setGroup($systemGroup);
            $this->manager->persist($warehouse);
        }
        /** @var Warehouse $warehouseAfter */
        foreach ($warehousesAfter as $warehouseAfter) {
            $warehouseAfter->setGroup($entity);
            $this->manager->persist($warehouseAfter);
        }
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    private function getSystemWarehouseGroup()
    {
        return $this->manager->getRepository(WarehouseGroup::class)->findOneBy(['system' => true]);
    }
}
