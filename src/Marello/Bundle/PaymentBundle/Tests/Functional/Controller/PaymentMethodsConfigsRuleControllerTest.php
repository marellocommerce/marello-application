<?php

namespace Marello\Bundle\PaymentBundle\Tests\Functional\Controller;

use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\PaymentBundle\Tests\Functional\Helper\PaymentTermIntegrationTrait;
use Marello\Bundle\RuleBundle\Entity\RuleInterface;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Marello\Bundle\PaymentBundle\Tests\Functional\DataFixtures\LoadPaymentMethodsConfigsRulesWithConfigs;
use Marello\Bundle\PaymentBundle\Tests\Functional\DataFixtures\LoadUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use Symfony\Component\DomCrawler\Form;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @group CommunityEdition
 */
class PaymentMethodsConfigsRuleControllerTest extends WebTestCase
{
    use PaymentTermIntegrationTrait;

    /**
     * @var PaymentMethodProviderInterface
     */
    protected $paymentMethodProvider;

    /**
     * @var Translator;
     */
    protected $translator;

    protected function setUp(): void
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                LoadPaymentMethodsConfigsRulesWithConfigs::class,
                LoadUserData::class
            ]
        );
        $this->paymentMethodProvider = static::getContainer()->get('marello_payment.payment_method.composite_provider');
        $this->translator = static::getContainer()->get('translator');
    }

    public function testIndexWithoutCreate()
    {
        $this->initClient([], static::generateBasicAuthHeader(LoadUserData::USER_VIEWER, LoadUserData::USER_VIEWER));
        $crawler = $this->client->request('GET', $this->getUrl('marello_payment_methods_configs_rule_index'));
        $result = $this->client->getResponse();
        static::assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertEquals(0, $crawler->selectLink('Create Payment Rule')->count());
    }

    /**
     * @return string
     */
    public function testCreate()
    {
        $this->initClient(
            [],
            static::generateBasicAuthHeader(LoadUserData::USER_VIEWER_CREATOR, LoadUserData::USER_VIEWER_CREATOR)
        );
        $crawler = $this->client->request('GET', $this->getUrl('marello_payment_methods_configs_rule_create'));

        /** @var Form $form */
        $form = $crawler
            ->selectButton('Save and Close')
            ->form();

        $name = 'New Rule';

        $formValues = $form->getPhpValues();
        $formValues['marello_payment_methods_configs_rule']['rule']['name'] = $name;
        $formValues['marello_payment_methods_configs_rule']['rule']['enabled'] = false;
        $formValues['marello_payment_methods_configs_rule']['currency'] = 'USD';
        $formValues['marello_payment_methods_configs_rule']['rule']['sortOrder'] = 1;
        $formValues['marello_payment_methods_configs_rule']['destinations'] =
            [
                [
                    'postalCodes' => '54321',
                    'country' => 'FR',
                    'region' => 'FR-75'
                ]
            ];
        $formValues['marello_payment_methods_configs_rule']['methodConfigs'] =
            [
                [
                    'method' => $this->getPaymentTermIdentifier(),
                    'options' => '',
                ]
            ];

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        static::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        $html = $crawler->html();

        $this->assertStringContainsString('Payment rule has been saved', $html);
        $this->assertStringContainsString('No', $html);

        return $name;
    }

    /**
     * @depends testCreate
     *
     * @param string $name
     */
    public function testIndex($name)
    {
        $auth = static::generateBasicAuthHeader(LoadUserData::USER_VIEWER_CREATOR, LoadUserData::USER_VIEWER_CREATOR);
        $this->initClient([], $auth);
        $crawler = $this->client->request('GET', $this->getUrl('marello_payment_methods_configs_rule_index'));
        $result = $this->client->getResponse();
        static::assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('marello-payment-methods-configs-rule-grid', $crawler->html());
        $href = $crawler->selectLink('Create Payment Rule')->attr('href');
        static::assertEquals($this->getUrl('marello_payment_methods_configs_rule_create'), $href);

        $response = $this->client->requestGrid(
            [
                'gridName' => 'marello-payment-methods-configs-rule-grid',
                'marello-payment-methods-configs-rule-grid[_sort_by][id]' => 'ASC',
            ]
        );

        static::getJsonResponseContent($response, 200);
    }

    /**
     * @depends testCreate
     *
     * @param string $name
     */
    public function testView($name)
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $paymentRule = $this->getPaymentMethodsConfigsRuleByName($name);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_payment_methods_configs_rule_view', ['id' => $paymentRule->getId()])
        );

        $result = $this->client->getResponse();
        static::assertHtmlResponseStatusCodeEquals($result, 200);

        $html = $crawler->html();

        $this->assertStringContainsString($paymentRule->getRule()->getName(), $html);
        $destination = $paymentRule->getDestinations();
        $this->assertStringContainsString((string)$destination[0], $html);
        $methodConfigs = $paymentRule->getMethodConfigs();
        $label = $this->paymentMethodProvider
            ->getPaymentMethod($methodConfigs[0]->getMethod())
            ->getLabel();
        $this->assertStringContainsString($this->translator->trans($label), $html);
    }

    /**
     * @depends testCreate
     *
     * @param string $name
     *
     * @return PaymentMethodsConfigsRule|object|null
     */
    public function testUpdate($name)
    {
        $paymentRule = $this->getPaymentMethodsConfigsRuleByName($name);

        $this->assertNotEmpty($paymentRule);

        $id = $paymentRule->getId();
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_payment_methods_configs_rule_update', ['id' => $id])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        $newName = 'New name for new rule';
        $formValues = $form->getPhpValues();
        $formValues['marello_payment_methods_configs_rule']['rule']['name'] = $newName;
        $formValues['marello_payment_methods_configs_rule']['rule']['enabled'] = false;
        $formValues['marello_payment_methods_configs_rule']['currency'] = 'USD';
        $formValues['marello_payment_methods_configs_rule']['rule']['sortOrder'] = 1;
        $formValues['marello_payment_methods_configs_rule']['destinations'] =
            [
                [
                    'postalCodes' => '54321',
                    'country' => 'TH',
                    'region' => 'TH-83'
                ]
            ];
        $formValues['marello_payment_methods_configs_rule']['methodConfigs'] =
            [
                [
                    'method' => $this->getPaymentTermIdentifier(),
                    'options' => '',
                ]
            ];

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        static::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);
        $html = $crawler->html();
        static::assertStringContainsString('Payment rule has been saved', $html);

        $paymentRule = $this->getPaymentMethodsConfigsRuleByName($newName);
        static::assertEquals($id, $paymentRule->getId());

        $destination = $paymentRule->getDestinations();
        static::assertEquals('TH', $destination[0]->getCountry()->getIso2Code());
        static::assertEquals('TH-83', $destination[0]->getRegion()->getCombinedCode());
        static::assertEquals('54321', $destination[0]->getPostalCodes()->current()->getName());
        $methodConfigs = $paymentRule->getMethodConfigs();
        static::assertEquals($this->getPaymentTermIdentifier(), $methodConfigs[0]->getMethod());
        static::assertFalse($paymentRule->getRule()->isEnabled());

        return $paymentRule;
    }

    /**
     * @depends testUpdate
     *
     * @param PaymentMethodsConfigsRule $paymentRule
     */
    public function testCancel(PaymentMethodsConfigsRule $paymentRule)
    {
        $paymentRule = $this->getPaymentMethodsConfigsRuleByName($paymentRule->getRule()->getName());

        $this->assertNotEmpty($paymentRule);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_payment_methods_configs_rule_update', ['id' => $paymentRule->getId()])
        );

        $link = $crawler->selectLink('Cancel')->link();
        $this->client->click($link);
        $response = $this->client->getResponse();

        static::assertHtmlResponseStatusCodeEquals($response, 200);

        $html = $response->getContent();

        static::assertStringContainsString($paymentRule->getRule()->getName(), $html);
        $destination = $paymentRule->getDestinations();
        static::assertStringContainsString((string)$destination[0], $html);
        $methodConfigs = $paymentRule->getMethodConfigs();
        $label = $this->paymentMethodProvider
            ->getPaymentMethod($methodConfigs[0]->getMethod())
            ->getLabel();
        static::assertStringContainsString($this->translator->trans($label), $html);
    }

    /**
     * @depends testUpdate
     *
     * @param PaymentMethodsConfigsRule $paymentRule
     *
     * @return PaymentMethodsConfigsRule
     */
    public function testUpdateRemoveDestination(PaymentMethodsConfigsRule $paymentRule)
    {
        $this->assertNotEmpty($paymentRule);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_payment_methods_configs_rule_update', ['id' => $paymentRule->getId()])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        $formValues = $form->getPhpValues();
        $formValues['marello_payment_methods_configs_rule']['destinations'] = [];

        $this->client->followRedirects(true);
        $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        static::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);
        $paymentRule = $this->getEntityManager()->find(
            'MarelloPaymentBundle:PaymentMethodsConfigsRule',
            $paymentRule->getId()
        );
        static::assertCount(0, $paymentRule->getDestinations());

        return $paymentRule;
    }

    public function testStatusDisableMass()
    {
        $this->initClient([], static::generateBasicAuthHeader());
        /** @var PaymentMethodsConfigsRule $paymentRule1 */
        $paymentRule1 = $this->getReference('payment_rule.1');
        /** @var PaymentMethodsConfigsRule $paymentRule2 */
        $paymentRule2 = $this->getReference('payment_rule.2');
        $url = $this->getUrl(
            'marello_payment_methods_configs_massaction',
            [
                'gridName' => 'marello-payment-methods-configs-rule-grid',
                'actionName' => 'disable',
                'inset' => 1,
                'values' => sprintf(
                    '%s,%s',
                    $paymentRule1->getId(),
                    $paymentRule2->getId()
                )
            ]
        );
        $this->ajaxRequest('POST', $url);
        $result = $this->client->getResponse();
        $data = json_decode($result->getContent(), true);
        $this->assertTrue($data['successful']);
        $this->assertSame(2, $data['count']);
        $this->assertFalse(
            $this
                ->getPaymentMethodsConfigsRuleById($paymentRule1->getId())
                ->getRule()
                ->isEnabled()
        );
        $this->assertFalse(
            $this
                ->getPaymentMethodsConfigsRuleById($paymentRule2->getId())
                ->getRule()
                ->isEnabled()
        );
    }

    /**
     * @depends testStatusDisableMass
     */
    public function testStatusEnableMass()
    {
        $this->initClient([], static::generateBasicAuthHeader());
        /** @var PaymentMethodsConfigsRule $paymentRule1 */
        $paymentRule1 = $this->getReference('payment_rule.1');
        /** @var PaymentMethodsConfigsRule $paymentRule2 */
        $paymentRule2 = $this->getReference('payment_rule.2');
        $url = $this->getUrl(
            'marello_payment_methods_configs_massaction',
            [
                'gridName' => 'marello-payment-methods-configs-rule-grid',
                'actionName' => 'enable',
                'inset' => 1,
                'values' => sprintf(
                    '%s,%s',
                    $paymentRule1->getId(),
                    $paymentRule2->getId()
                )
            ]
        );
        $this->ajaxRequest('POST', $url);
        $result = $this->client->getResponse();
        $data = json_decode($result->getContent(), true);
        $this->assertTrue($data['successful']);
        $this->assertSame(2, $data['count']);
        $this->assertTrue(
            $this
                ->getPaymentMethodsConfigsRuleById($paymentRule1->getId())
                ->getRule()
                ->isEnabled()
        );
        $this->assertTrue(
            $this
                ->getPaymentMethodsConfigsRuleById($paymentRule2->getId())
                ->getRule()
                ->isEnabled()
        );
    }

    public function testPaymentMethodsConfigsRuleEditWOPermission()
    {
        $authParams = static::generateBasicAuthHeader(LoadUserData::USER_VIEWER, LoadUserData::USER_VIEWER);
        $this->initClient([], $authParams);

        /** @var PaymentMethodsConfigsRule $paymentRule */
        $paymentRule = $this->getReference('payment_rule.1');

        $this->client->request(
            'GET',
            $this->getUrl('marello_payment_methods_configs_rule_update', ['id' => $paymentRule->getId()])
        );

        static::assertJsonResponseStatusCodeEquals($this->client->getResponse(), 403);
    }

    public function testPaymentMethodsConfigsRuleEdit()
    {
        $authParams = static::generateBasicAuthHeader(LoadUserData::USER_EDITOR, LoadUserData::USER_EDITOR);
        $this->initClient([], $authParams);

        /** @var PaymentMethodsConfigsRule $paymentRule */
        $paymentRule = $this->getReference('payment_rule.1');

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_payment_methods_configs_rule_update', ['id' => $paymentRule->getId()])
        );

        static::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();

        $rule = $paymentRule->getRule();
        $form['marello_payment_methods_configs_rule[rule][enabled]'] = !$rule->isEnabled();
        $form['marello_payment_methods_configs_rule[rule][name]'] = $rule->getName() . ' new name';
        $form['marello_payment_methods_configs_rule[rule][sortOrder]'] = $rule->getSortOrder() + 1;
        $form['marello_payment_methods_configs_rule[currency]'] = $paymentRule->getCurrency() === 'USD' ? 'EUR' : 'USD';
        $form['marello_payment_methods_configs_rule[rule][stopProcessing]'] = !$rule->isStopProcessing();
        $form['marello_payment_methods_configs_rule[destinations][0][postalCodes]'] = '11111';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        static::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);
        static::assertStringContainsString('Payment rule has been saved', $crawler->html());
    }

    public function testDeleteButtonNotVisible()
    {
        $authParams = static::generateBasicAuthHeader(LoadUserData::USER_VIEWER, LoadUserData::USER_VIEWER);
        $this->initClient([], $authParams);

        $response = $this->client->requestGrid(
            ['gridName' => 'marello-payment-methods-configs-rule-grid'],
            [],
            true
        );

        $result = static::getJsonResponseContent($response, 200);

        $this->assertEquals(false, isset($result['metadata']['massActions']['delete']));
    }

    /**
     * @return ObjectManager|null
     */
    protected function getEntityManager()
    {
        return static::getContainer()
            ->get('doctrine')
            ->getManagerForClass('MarelloPaymentBundle:PaymentMethodsConfigsRule');
    }

    /**
     * @param string $name
     *
     * @return PaymentMethodsConfigsRule|null
     */
    protected function getPaymentMethodsConfigsRuleByName($name)
    {
        /** @var RuleInterface $rule */
        $rule = $this
            ->getEntityManager()
            ->getRepository('MarelloRuleBundle:Rule')
            ->findOneBy(['name' => $name]);

        return $this
            ->getEntityManager()
            ->getRepository('MarelloPaymentBundle:PaymentMethodsConfigsRule')
            ->findOneBy(['rule' => $rule]);
    }

    /**
     * @param int $id
     *
     * @return PaymentMethodsConfigsRule|null
     */
    protected function getPaymentMethodsConfigsRuleById($id)
    {
        return $this->getEntityManager()
            ->getRepository('MarelloPaymentBundle:PaymentMethodsConfigsRule')
            ->find($id);
    }
}
