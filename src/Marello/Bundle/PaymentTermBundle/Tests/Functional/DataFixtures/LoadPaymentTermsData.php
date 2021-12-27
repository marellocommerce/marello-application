<?php

namespace Marello\Bundle\PaymentTermBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\PaymentTermBundle\DependencyInjection\Configuration;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Entity\Config;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadPaymentTermsData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const PAYMENT_TERM_1_REF = 'paymentTerm1';
    const PAYMENT_TERM_2_REF = 'paymentTerm2';
    const PAYMENT_TERM_3_REF = 'paymentTerm3';
    const PAYMENT_TERM_4_REF = 'paymentTerm4';

    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /** @var ConfigManager $configManager */
    protected $configManager;

    /**
     * @var array
     */
    protected $data = [
        self::PAYMENT_TERM_1_REF => [
            'code' => 'term1',
            'term' => 1,
            'label' => 'term label 1',
        ],
        self::PAYMENT_TERM_2_REF => [
            'code' => 'term2',
            'term' => 2,
            'label' => 'term label 2',
        ],
        self::PAYMENT_TERM_3_REF => [
            'code' => 'term3',
            'term' => 3,
            'label' => 'term label 3',
        ],
        self::PAYMENT_TERM_4_REF => [
            'code' => 'term4',
            'term' => 4,
            'label' => 'term label 4',
        ],
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
        foreach ($this->data as $ref => $values) {
            $paymentTerm = $this->buildPaymentTerm($values);

            $this->manager->persist($paymentTerm);
            $this->setReference($ref, $paymentTerm);
        }

        $this->manager->flush();
    }

    protected function setDefaultPaymentTerm()
    {
        /** @var Config $config */
        $this->container
            ->get('oro_config.manager')
            ->set(
                Configuration::DEFAULT_PAYMENT_TERM_CONFIG_PATH,
                (string)$this->getReference(self::PAYMENT_TERM_1_REF)->getId()
            )
        ;
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
