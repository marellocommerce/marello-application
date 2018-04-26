<?php

namespace Marello\Bundle\MageBridgeBundle\Form\Type;

//use Marello\Bundle\MageBridgeBundle\Form\Transformer\ArrayToJsonTransformer;
use Marello\Bundle\MageBridgeBundle\Provider\MagentoStoreList;
use Marello\Bundle\MageBridgeBundle\Provider\SalesChannelProvider;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\EntityConfigBundle\Provider\SerializedFieldProvider;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;


class WebsiteSalesChannelType extends AbstractType
{
    const NAME = 'website_sales_channel_grid';
    const CONFIG_FORM_NAME = 'marello_mage_bridge___magento_website_sales_channel';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected $magentoStoreProvider;

    protected $salesChannelProvider;

    public function __construct(
        ConfigManager $configManager,
        MagentoStoreList $serializedFieldProvider,
        SalesChannelProvider $salesChannelProvider
    ) {
        $this->configManager        = $configManager;
        $this->magentoStoreProvider = $serializedFieldProvider;
        $this->salesChannelProvider = $salesChannelProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\HiddenType';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ArrayToJsonTransformer());



        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'rebuildFormElement']);
    }


    /**
     * Rebuild form element
     * "default_currency" for save dynamic data
     * from allowed_currencies
     *
     * @param FormEvent $formEvent
     *
     * @return bool | void
     */
    public function rebuildFormElement(FormEvent $formEvent)
    {
        /**
         * If transformation is failed then we use empty array as default value
         */
        $data = is_array($formEvent->getData()) ? $formEvent->getData() : [];


        file_put_contents('/app/app/logs/debug.log', print_r($data, true), FILE_APPEND | LOCK_EX);
        file_put_contents('/app/app/logs/debug.log', PHP_EOL, FILE_APPEND | LOCK_EX);
        file_put_contents('/app/app/logs/debug.log', print_r(__METHOD__ .'###'. __LINE__, true), FILE_APPEND | LOCK_EX);
        file_put_contents('/app/app/logs/debug.log', PHP_EOL, FILE_APPEND | LOCK_EX);



    }


    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['magentoStoreList'] = $this->magentoStoreProvider->getMagentoStoreList();
        $view->vars['salesChannelList'] = $this->salesChannelProvider->getSalesChannelList();
    }
}
