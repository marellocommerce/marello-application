<?php

namespace Marello\Bundle\SalesBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SalesChannelHandler implements FormHandlerInterface
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
        if (!$data instanceof SalesChannel) {
            throw new \InvalidArgumentException('Argument data should be instance of SalesChannel entity');
        }
        
        $form->setData($data);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $form->submit($request);
            $createOwnGroup = false;
            if ($form->has('createOwnGroup')) {
                $createOwnGroup = $form->get('createOwnGroup')->getData();
            }

            if ($form->isValid()) {
                $this->onSuccess($data, $createOwnGroup);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param SalesChannel $entity
     * @param bool $createOwnGroup
     */
    protected function onSuccess(SalesChannel $entity, $createOwnGroup = false)
    {
        if ($createOwnGroup) {
            $group = $this->createOwnGroup($entity);
            $entity->setGroup($group);
        }

        $this->manager->persist($entity);
        $this->manager->flush();
    }


    /**
     * @param SalesChannel $entity
     * @return SalesChannelGroup
     */
    private function createOwnGroup(SalesChannel $entity)
    {
        $name = $entity->getName();
        $group = new SalesChannelGroup();
        $group
            ->setName($name)
            ->setOrganization($entity->getOwner())
            ->setDescription(sprintf('%s group', $name))
            ->setSystem(false);

        $this->manager->persist($group);
        $this->manager->flush($group);

        return $group;
    }
}
