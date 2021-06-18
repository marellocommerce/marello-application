<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\PaymentTermBundle\DependencyInjection\Configuration;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadPaymentTermData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const DEFAULT_PAYMENT_TERM_CODE = 'net_10';

    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * @var array $data
     */
    protected $data = [
        [
            'label' => 'Net 10',
            'code' => self::DEFAULT_PAYMENT_TERM_CODE,
            'term' => 10
        ],
        [
            'label' => 'Net 14',
            'code' => 'net_14',
            'term' => 14
        ],
        [
            'label' => 'Net 30',
            'code' => 'net_30',
            'term' => 30
        ],
        [
            'label' => 'Net 60',
            'code' => 'net_60',
            'term' => 60
        ],
        [
            'label' => 'Net 90',
            'code' => 'net_90',
            'term' => 90
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadPaymentTerms();
        $this->setDefaultPaymentTerm();
    }

    /**
     * load and create PaymentTerms
     */
    protected function loadPaymentTerms()
    {
        foreach ($this->data as $values) {
            $paymentTerm = $this->buildPaymentTerm($values);

            $this->manager->persist($paymentTerm);
            $this->setReference($values['code'], $paymentTerm);
        }

        $this->manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function setDefaultPaymentTerm()
    {
        $configManager = $this->container->get('oro_config.global');
        $configManager->set(
            Configuration::DEFAULT_PAYMENT_TERM_CONFIG_PATH,
            (string)$this->getReference(self::DEFAULT_PAYMENT_TERM_CODE)->getId()
        );
        $configManager->flush();
    }

    /**
     * @param array  $data
     *
     * @return PaymentTerm
     */
    private function buildPaymentTerm($data)
    {
        $paymentTerm = new PaymentTerm();
        $paymentTerm
            ->setCode($data['code'])
            ->setTerm($data['term'])
            ->addLabel((new LocalizedFallbackValue())->setString($data['label']))
        ;

        return $paymentTerm;
    }
}
