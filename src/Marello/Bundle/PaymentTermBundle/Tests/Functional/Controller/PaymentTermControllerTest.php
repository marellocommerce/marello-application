<?php

namespace Marello\Bundle\PaymentTermBundle\Tests\Functional\Controller;

use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Marello\Bundle\PaymentTermBundle\Form\Type\PaymentTermType;
use Marello\Bundle\PaymentTermBundle\Tests\Functional\DataFixtures\LoadPaymentTermsData;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class PaymentTermControllerTest extends WebTestCase
{
    const CODE = 'code';
    const TERM = '14';
    const LABEL = 'label';

    const UPDATED_CODE = 'updatedCode';
    const UPDATED_TERM = '30';
    const UPDATED_LABEL = 'updatedLabel';

    const SAVE_MESSAGE = 'Payment term has been saved';

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadPaymentTermsData::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testIndex()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_paymentterm_paymentterm_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertStringContainsString('marello-paymentterm-grid', $crawler->html());

        $response = $this->client->requestGrid('marello-paymentterm-grid');
        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $this->assertCount(4, $result['data']);
    }

    /**
     * {@inheritdoc}
     * @return int
     */
    public function testCreate()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_paymentterm_paymentterm_create')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertPaymentTermSave(
            $crawler,
            self::CODE,
            self::TERM,
            self::LABEL
        );

        /** @var PaymentTerm $paymentTerm */
        $paymentTerm = $this->getContainer()->get('doctrine')
            ->getManagerForClass(PaymentTerm::class)
            ->getRepository(PaymentTerm::class)
            ->findOneBy(['code' => self::CODE]);
        $this->assertNotEmpty($paymentTerm);

        return $paymentTerm->getId();
    }

    /**
     * {@inheritdoc}
     * @param int $id
     * @return int
     * @depends testCreate
     */
    public function testUpdate($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_paymentterm_paymentterm_update', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertPaymentTermSave(
            $crawler,
            self::UPDATED_CODE,
            self::UPDATED_TERM,
            self::UPDATED_LABEL
        );

        return $id;
    }

    /**
     * {@inheritdoc}
     * @depends testUpdate
     * @param int $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_paymentterm_paymentterm_view', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertViewPage($crawler->html(), self::UPDATED_CODE, self::UPDATED_TERM, self::UPDATED_LABEL);
    }

    /**
     * {@inheritdoc}
     * @param Crawler $crawler
     * @param string $code
     * @param string $term
     * @param PaymentTerm[] $paymentTerms
     */
    protected function assertPaymentTermSave(Crawler $crawler, $code, $term, $label)
    {
        $labels = [];
        foreach ($this->getLocalizations() as $localization) {
            $labels[$localization->getId()] = [
                'use_fallback' => true,
                'fallback' => $localization->getParentLocalization() === null ? 'system' : 'parent_localization',
            ];
        }

        $form = $crawler->selectButton('Save and Close')->form();
        $formData = $form->getPhpValues();
        $formData['input_action'] = '{"route":"marello_paymentterm_paymentterm_view","params":{"id":"$id"}}';
        $formData[PaymentTermType::BLOCK_PREFIX]['code'] = $code;
        $formData[PaymentTermType::BLOCK_PREFIX]['term'] = $term;
        $formData[PaymentTermType::BLOCK_PREFIX]['labels'] = [
            'values' => [
                'default' => $label,
                'localizations' => $labels,
            ],
        ];

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formData);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        $this->assertStringContainsString(self::SAVE_MESSAGE, $html);
        $this->assertViewPage($html, $code, $term, $label);
    }

    /**
     * {@inheritdoc}
     * @param string $html
     * @param string $code
     * @param string $term
     * @param string $label
     */
    protected function assertViewPage($html, $code, $term, $label)
    {
        $this->assertStringContainsString($code, $html);
        $this->assertStringContainsString($term, $html);
        $this->assertStringContainsString($label, $html);
    }

    protected function getLocalizations()
    {
        return $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository(Localization::class)
            ->findAll()
        ;
    }
}
