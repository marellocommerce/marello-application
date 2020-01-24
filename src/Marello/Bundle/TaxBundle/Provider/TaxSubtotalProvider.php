<?php

namespace Marello\Bundle\TaxBundle\Provider;

use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\PricingBundle\Subtotal\Provider\SubtotalProviderInterface;
use Marello\Bundle\TaxBundle\Event\TaxEventDispatcher;
use Marello\Bundle\TaxBundle\Factory\TaxFactory;
use Marello\Bundle\TaxBundle\Model\Result;
use Marello\Bundle\TaxBundle\Model\Taxable;
use Symfony\Component\Translation\TranslatorInterface;

class TaxSubtotalProvider implements SubtotalProviderInterface
{
    const TYPE = 'tax';
    const NAME = 'marello_tax.subtotal_tax';
    const SUBTOTAL_ORDER = 50;

    /**
     * @var TranslatorInterface
     */
    protected $translator;
    
    /**
     * @var TaxEventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var TaxFactory
     */
    protected $taxFactory;

    /**
     * @param TranslatorInterface $translator
     * @param TaxEventDispatcher $eventDispatcher
     * @param TaxFactory $taxFactory
     */
    public function __construct(
        TranslatorInterface $translator,
        TaxEventDispatcher $eventDispatcher,
        TaxFactory $taxFactory
    ) {
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->taxFactory = $taxFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubtotal($entity)
    {
        $subtotal = $this->createSubtotal();

        try {
            $tax = $this->getTax($entity);
            $this->fillSubtotal($subtotal, $tax);
        } catch (\Exception $e) {
        }

        return $subtotal;
    }

    /**
     * @return Subtotal
     */
    protected function createSubtotal()
    {
        $subtotal = new Subtotal([]);

        $subtotal->setType(self::TYPE);
        $label = sprintf('marello.tax.subtotals.%s.label', self::TYPE);
        $subtotal->setLabel($this->translator->trans($label));
        $subtotal->setSortOrder(self::SUBTOTAL_ORDER);

        return $subtotal;
    }

    /**
     * @param Subtotal $subtotal
     * @param Result $tax
     * @return Subtotal
     */
    protected function fillSubtotal(Subtotal $subtotal, Result $tax)
    {
        $subtotal->setAmount($tax->getTotal()->getTaxAmount());
        $subtotal->setCurrency($tax->getTotal()->getCurrency());
        $subtotal->setVisible((bool)$tax->getTotal()->getTaxAmount());

        return $subtotal;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($entity)
    {
        return $this->taxFactory->supports($entity);
    }

    /**
     * @param object $object
     * @return Result
     */
    public function getTax($object)
    {
        return $this->getTaxable($object)->getResult();
    }

    /**
     * @param object $object
     * @return Taxable
     */
    protected function getTaxable($object)
    {
        $taxable = $this->taxFactory->create($object);
        $taxable->setResult(new Result());

        $this->eventDispatcher->dispatch($taxable);

        return $taxable;
    }
}
