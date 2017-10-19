<?php

namespace Marello\Bundle\InventoryBundle\Command;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InventoryBalanceCommand extends ContainerAwareCommand
{
    const NAME = 'marello:inventory:rebalance';
    const ALL = 'all';
    const PRODUCT = 'product';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->addOption(self::ALL)
            ->addOption(
                self::PRODUCT,
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'product ids for rebalancing inventory',
                []
            )
            ->setDescription('Rebalance inventory for SalesChannel (Groups)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $optionAll = (bool)$input->getOption(self::ALL);
        if ($optionAll) {
            $this->processAllProducts($output);
        } elseif ($input->getOption(self::PRODUCT)) {
            $this->processProducts($input, $output);
        } else {
            $output->writeln(
                '<comment>ATTENTION</comment>: To update all products run command with <info>--all</info> option:'
            );
            $output->writeln(sprintf('    <info>%s --all</info>', $this->getName()));
        }
    }

    /**
     * @param OutputInterface $output
     */
    protected function processAllProducts(OutputInterface $output)
    {
        $output->writeln('<info>Start processing of all Products for rebalancing</info>');

//        $this->buildPriceRulesForAllPriceLists();
//
//        $output->writeln('<info>Start combining all Price Lists</info>');
//        $this->getContainer()->get('oro_pricing.builder.combined_price_list_builder')->build(true);
//        $output->writeln('<info>The cache is updated successfully</info>');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function processProducts(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start processing of Products for rebalancing</info>');
        $products = $this->getProducts($input);
        $output->writeln(sprintf('<info>count of products %s</info>', count($products)));
        /** @var Product $product */
        foreach ($products as $product) {
            $channels = $product->getChannels();
            $channelGroups = [];

            /** @var InventoryItem $inventoryItem */
            $inventoryItem = $product->getInventoryItems()->first();
            /** @var InventoryLevel[] $levels */
            $levels = $inventoryItem->getInventoryLevels();
            $whs = [];
            $scgs = [];
            /** @var InventoryLevel $level */
            foreach ($levels as $level) {
                $whs[$level->getWarehouse()->getGroup()->getId()] = $level->getWarehouse()->getGroup()->getId();
                $scgs[] = $level->getWarehouse()->getGroup()->getWarehouseChannelGroupLink()->getSalesChannelGroups();
            }
                $output->writeln(count($channels));

            /** @var SalesChannel $channel */
            foreach ($channels as $channel) {
                $output->writeln($this->getWarehouseGroupsByChannelGroup($channel->getId()));
                $channelGroups[$channel->getGroup()->getId()] = $channel->getGroup();
            }
        }
//        $priceLists = $this->getPriceLists($input);
//
//        $output->writeln('<info>Start the process Price rules</info>');
//        $this->buildPriceRulesByPriceLists($priceLists);
//
//        $output->writeln('<info>Start combining Price Lists</info>');
//        $this->buildCombinedPriceListsByPriceLists($priceLists);
//        $output->writeln('<info>The cache is updated successfully</info>');
    }

    protected function getWarehouseGroupsByChannelGroup($channelGroupId)
    {
        $registry = $this->getContainer()->get('doctrine');
        /** @var EntityRepository $warehouseGroupRepository */
        $warehouseGroupRepository = $registry
            ->getManagerForClass(WarehouseGroup::class)
            ->getRepository(WarehouseGroup::class);

        $queryBuilder = $warehouseGroupRepository->createQueryBuilder('whg');
        $queryBuilder
            ->leftJoin('whg.warehouseChannelGroupLink', 'wcgl')
            ->orderBy('whg.name', 'ASC')
            ->where('wcgl.id IS NULL');


        /** @var Product[] $products */
        return $queryBuilder->getQuery()->getSQL();
    }

    /**
     * @param InputInterface $input
     * @return mixed
     */
    protected function getProducts(InputInterface $input)
    {
        $productIds = $input->getOption(self::PRODUCT);
        $registry = $this->getContainer()->get('doctrine');
        /** @var ProductRepository $productRepository */
        $productRepository = $registry
            ->getManagerForClass(Product::class)
            ->getRepository(Product::class);

        /** @var Product[] $products */
        return $productRepository->findBy(['id' => $productIds]);
    }

    /**
     * @param InputInterface $input
     * @return array|Website[]
     */
    protected function getWebsites(InputInterface $input)
    {
//        $websiteIds = $input->getOption(self::WEBSITE);
//        /** @var WebsiteRepository $repository */
//        $repository = $this->getContainer()->get('doctrine')
//            ->getManagerForClass(Website::class)
//            ->getRepository(Website::class);
//        if (count($websiteIds) === 0) {
//            $websites = $repository->findAll();
//        } else {
//            $websites = $repository->findBy(['id' => $websiteIds]);
//        }
//
//        return $websites;
    }

    /**
     * @param InputInterface $input
     * @return array|CustomerGroup[]
     */
    protected function getCustomerGroups(InputInterface $input)
    {
//        $customerGroupIds = $input->getOption(self::ACCOUNT_GROUP);
//        /** @var CustomerGroupRepository $repository */
//        $repository = $this->getContainer()->get('doctrine')
//            ->getManagerForClass(CustomerGroup::class)
//            ->getRepository(CustomerGroup::class);
//        $customerGroups = [];
//        if (count($customerGroupIds)) {
//            $customerGroups = $repository->findBy(['id' => $customerGroupIds]);
//        }
//
//        return $customerGroups;
    }

    /**
     * @param InputInterface $input
     * @return array|Customer[]
     */
    protected function getCustomers(InputInterface $input)
    {
//        $customerIds = $input->getOption(self::ACCOUNT);
//        /** @var CustomerRepository $repository */
//        $repository = $this->getContainer()->get('doctrine')
//            ->getManagerForClass(Customer::class)
//            ->getRepository(Customer::class);
//        $customers = [];
//        if (count($customerIds)) {
//            $customers = $repository->findBy(['id' => $customerIds]);
//        }
//
//        return $customers;
    }
}
