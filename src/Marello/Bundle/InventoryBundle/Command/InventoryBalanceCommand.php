<?php

namespace Marello\Bundle\InventoryBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Marello\Bundle\InventoryBundle\Async\Topics;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

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
            ->setDescription('Rebalance inventory for Product(s)');
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
     * Process all products for rebalancing
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

        if (count($products) <= 0) {
            $output->writeln(sprintf('<error>No products found, did you add products first?</error>'));
            return;
        }

        $output->writeln(sprintf('<info>count of products %s</info>', count($products)));
        $this->triggerInventoryBalancer($products, $output);
    }

    /**
     * Process products based on the user input
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function processProducts(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start processing of Products for rebalancing</info>');
        $products = $this->getProducts($input);
        $output->writeln(sprintf('<info>count of products %s</info>', count($products)));
        $this->triggerInventoryBalancer($products, $output);
    }

    /**
     * Get products based on the user input
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

        $products = [];
        if (count($productIds)) {
            $products = $productRepository->findBy(['id' => $productIds]);
        }

        /** @var Product[] $products */
        return $products;
    }

    /**
     * Trigger inventory balancer for products
     * @param Product[] $products
     * @param OutputInterface $output
     */
    protected function triggerInventoryBalancer($products, OutputInterface $output)
    {
        $inventoryBalancer = $this->getContainer()->get('marello_inventory.model.balancer.inventory_balancer');

        /** @var Product $product */
        foreach ($products as $product) {
            $output->writeln(sprintf('<info>processing product sku %s</info>', $product->getSku()));
            // balance 'Global' && 'Virtual' Warehouses
            $inventoryBalancer->balanceInventory($product, false, true);

            // balance 'Fixed' warehouses
            $inventoryBalancer->balanceInventory($product, true, true);
        }
    }
}
