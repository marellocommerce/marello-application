<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Provider\ReplenishmentOrdersFromConfigProvider;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ReplenishmentOrderConfigHandler
{
    use RequestHandlerTrait;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var ReplenishmentOrdersFromConfigProvider
     */
    protected $replenishmentOrdersProvider;

    /**
     * @param FormInterface $form
     * @param RequestStack $requestStack
     * @param ObjectManager $manager
     * @param ReplenishmentOrdersFromConfigProvider $replenishmentOrdersProvider
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        ObjectManager $manager,
        ReplenishmentOrdersFromConfigProvider $replenishmentOrdersProvider
    ) {
        $this->form = $form;
        $this->request = $requestStack->getCurrentRequest();
        $this->manager = $manager;
        $this->replenishmentOrdersProvider = $replenishmentOrdersProvider;
    }

    /**
     * Process form
     *
     * @param  ReplenishmentOrderConfig $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(ReplenishmentOrderConfig $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($this->form, $this->request);

            if ($this->form->isValid()) {
                return $this->onSuccess($entity);
            }
        }

        return [
            'result' => false,
        ];
    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    public function getFormView()
    {
        return $this->form->createView();
    }

    /**
     * "Success" form handler
     *
     * @param ReplenishmentOrderConfig $entity
     * @return array
     */
    protected function onSuccess(ReplenishmentOrderConfig $entity)
    {
        if (!$entity->getExecutionDateTime()) {
            $entity->setExecutionDateTime(new \DateTime());
        }
        $this->manager->persist($entity);
        $orders = $this->replenishmentOrdersProvider->getReplenishmentOrders($entity);

        if (empty($orders)) {
            return [
                'result' => true,
                'messageType' => 'info',
                'message'
                    => 'marelloenterprise.replenishment.replenishmentorderconfig.messages.info.no_products_in_origins'
            ];
        }

        if ($entity->getId()) {
            $existingOrders = $this->manager->getRepository(ReplenishmentOrder::class)->findByConfig($entity->getId());
            foreach ($existingOrders as $existingOrder) {
                $this->manager->remove($existingOrder);
            }
        }
        foreach ($orders as $order) {
            $this->manager->persist($order);
        }
        $this->manager->flush();

        return [
            'result' => true,
            'messageType' => 'success',
            'message' => 'marelloenterprise.replenishment.replenishmentorderconfig.messages.success.saved'
        ];
    }
}
