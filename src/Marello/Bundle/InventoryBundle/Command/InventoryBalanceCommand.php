<?php

namespace Marello\Bundle\InventoryBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\InventoryBundle\Strategy\EqualDivision\EqualDivisionBalancerStrategy;
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

        $registry = $this->getContainer()->get('doctrine');
        /** @var Product[] $products */
        $products = $registry
            ->getManagerForClass(Product::class)
            ->getRepository(Product::class)
            ->findAll();
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
            /** @var InventoryItem $inventoryItem */
            $inventoryItem = $product->getInventoryItems()->first();
            /** @var InventoryLevel[]|ArrayCollection $levels */
            $nonFixedWarehouseLevels = $inventoryItem->getInventoryLevels()->filter(function($level) {
                /** @var InventoryLevel $level */
                return ($level->getWarehouse()->getWarehouseType()->getName() !== 'fixed' && $level->getWarehouse()->getGroup()->getWarehouseChannelGroupLink() !== null);
            });

            $sortedWhgLevels = [];
            $linkedWhgToScgs = [];
            $nonFixedWarehouseLevels->map(function($level) use (&$sortedWhgLevels, &$linkedWhgToScgs) {
                /** @var InventoryLevel $level */

                /** @var WarehouseGroup $whg */
                $whg = $level->getWarehouse()->getGroup();
                if (!array_key_exists($whg->getId(), $sortedWhgLevels)) {
                    $sortedWhgLevels[$whg->getId()] = $level->getVirtualInventoryQty();
                } else {
                    $sortedWhgLevels[$whg->getId()] += $level->getVirtualInventoryQty();
                }

                $linkedWhgToScgs[$whg->getId()] = $linkedSalesChannelGroups = $whg->getWarehouseChannelGroupLink()->getSalesChannelGroups();


            });

            $strategy = new EqualDivisionBalancerStrategy();
            foreach ($linkedWhgToScgs as $whgId => $scgs) {
                $inventoryTotalForWhg = $sortedWhgLevels[$whgId];
                $result = $strategy->getBalancedResult($product, $scgs, $inventoryTotalForWhg);
                var_dump($result);
            }
        }
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

        var_dump(count($productIds));
        $products = [];
        if (count($productIds)) {
            $products = $productRepository->findBy(['id' => $productIds]);
        }

        /** @var Product[] $products */
        return $products;
    }
}
