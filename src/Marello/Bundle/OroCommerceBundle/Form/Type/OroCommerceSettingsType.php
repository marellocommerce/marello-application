<?php

namespace Marello\Bundle\OroCommerceBundle\Form\Type;

use Doctrine\Common\Cache\CacheProvider;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\Generator\CacheKeyGenerator;
use Marello\Bundle\OroCommerceBundle\Generator\CacheKeyGeneratorInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\CurrencyBundle\Form\Type\CurrencyType;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class OroCommerceSettingsType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_orocommerce_settings';

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var CacheKeyGeneratorInterface
     */
    protected $cacheKeyGenerator;

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @param CacheProvider $cache
     * @param CacheKeyGeneratorInterface $cacheKeyGenerator
     * @param LocaleSettings $localeSettings
     */
    public function __construct(
        CacheProvider $cache,
        CacheKeyGeneratorInterface $cacheKeyGenerator,
        LocaleSettings $localeSettings
    ) {
        $this->cache = $cache;
        $this->cacheKeyGenerator = $cacheKeyGenerator;
        $this->localeSettings = $localeSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'url',
                TextType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.url.label',
                    'required' => true,
                    'tooltip' => 'marello.orocommerce.orocommercesettings.url.tooltip',
                ]
            )
            ->add(
                'userName',
                TextType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.username.label',
                    'required' => true
                ]
            )
            ->add(
                'key',
                TextType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.key.label',
                    'required' => true
                ]
            )
            ->add(
                'currency',
                CurrencyType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.currency.label',
                    'required' => true
                ]
            )
            ->add(
                'salesChannelGroup',
                EntityType::class,
                [
                    'label'         => 'marello.orocommerce.orocommercesettings.saleschannelgroup.label',
                    'class'         => SalesChannelGroup::class,
                    'required'      => true
                ]
            )
            ->add(
                'enterprise',
                CheckboxType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.enterprise.label',
                    'required' => false
                ]
            )
            ->add(
                'warehouse',
                ChoiceType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.warehouse.label',
                    'required' => false
                ]
            )
            ->add(
                'businessUnit',
                ChoiceType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.business_unit.label',
                    'required' => true
                ]
            )
            ->add(
                'productUnit',
                ChoiceType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.product_unit.label',
                    'required' => true
                ]
            )
            ->add(
                'customerTaxCode',
                ChoiceType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.customer_tax_code.label',
                    'required' => true
                ]
            )
            ->add(
                'priceList',
                ChoiceType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.price_list.label',
                    'required' => true
                ]
            )
            ->add(
                'productFamily',
                ChoiceType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.product_family.label',
                    'required' => true
                ]
            )
            ->add(
                'deleteRemoteDataOnDeactivation',
                CheckboxType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.delete_remote_data_on_deactivation.label',
                    'required' => false
                ]
            )
            ->add(
                'deleteRemoteDataOnDeletion',
                CheckboxType::class,
                [
                    'label' => 'marello.orocommerce.orocommercesettings.delete_remote_data_on_deletion.label',
                    'required' => false
                ]
            )
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [$this, 'onPreSet']
            )
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                [$this, 'onPreSubmit']
            );
    }

    /**
     * Preset data for channels
     * @param FormEvent $event
     */
    public function onPreSet(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var OroCommerceSettings $data */
        $data = $event->getData();

        if ($data !== null) {
            $currency = $data->getCurrency();
        }

        if (!($data && $data->getId())) {
            $currency = $this->localeSettings->getCurrency();
        }

        if (isset($currency)) {
            FormUtils::replaceField($form, 'currency', ['data' => $currency]);
        }

        $event->setData($data);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        $paramBag = new ParameterBag([
            OroCommerceSettings::URL_FIELD => $data['url'],
            OroCommerceSettings::USERNAME_FIELD => $data['userName'],
            OroCommerceSettings::KEY_FIELD => $data['key']
        ]);

        $key = $this->cacheKeyGenerator->generateKey($paramBag);
        $businessUnitKey = sprintf('%s_%s', $key, CacheKeyGenerator::BUSINESS_UNIT);
        $productUnitKey = sprintf('%s_%s', $key, CacheKeyGenerator::PRODUCT_UNIT);
        $customerTaxCodeKey = sprintf('%s_%s', $key, CacheKeyGenerator::CUSTOMER_TAX_CODE);
        $priceListKey = sprintf('%s_%s_%s', $key, CacheKeyGenerator::PRICE_LIST, $data['currency']);
        $productFamilyKey = sprintf('%s_%s', $key, CacheKeyGenerator::PRODUCT_FAMILY);
        $warehouseKey = sprintf('%s_%s', $key, CacheKeyGenerator::WAREHOUSE);

        $this->updateFormWithCachedData($businessUnitKey, $form, 'businessUnit', 'business_unit');
        $this->updateFormWithCachedData($productUnitKey, $form, 'productUnit', 'product_unit');
        $this->updateFormWithCachedData($customerTaxCodeKey, $form, 'customerTaxCode', 'customer_tax_code');
        $this->updateFormWithCachedData($priceListKey, $form, 'priceList', 'price_list');
        $this->updateFormWithCachedData($productFamilyKey, $form, 'productFamily', 'product_family');
        $this->updateFormWithCachedData($warehouseKey, $form, 'warehouse', 'warehouse');
    }

    /**
     * @param string $key
     * @param FormInterface $form
     * @param string $field
     * @param string $labelSuffix
     */
    private function updateFormWithCachedData($key, FormInterface $form, $field, $labelSuffix)
    {
        if ($this->cache->contains($key)) {
            $choices = [];
            $results = $this->cache->fetch($key);
            foreach ($results as $result) {
                $choices[$result['label']] = $result['value'];
            }
            $form->remove($field);
            $form->add(
                $field,
                ChoiceType::class,
                [
                    'label' => sprintf('marello.orocommerce.orocommercesettings.%s.label', $labelSuffix),
                    'required' => true,
                    'choices' => $choices
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OroCommerceSettings::class,
            'constraints' => new Valid(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
