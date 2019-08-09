<?php

namespace Marello\Bundle\InventoryBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancer;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InventoryBalanceCommand extends Command
{
    const NAME = 'marello:inventory:rebalance';
    const ALL = 'all';
    const PRODUCT = 'product';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var InventoryBalancer
     */
    private $inventoryBalancer;
    
    public function __construct(Registry $registry, InventoryBalancer $inventoryBalancer)
    {
        parent::__construct();
        
        $this->registry = $registry;
        $this->inventoryBalancer = $inventoryBalancer;
    }

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

        /** @var Product[] $products */
        $products = $this->registry
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
        /** @var ProductRepository $productRepository */
        $productRepository = $this->registry
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
        /** @var Product $product */
        foreach ($products as $product) {
            $output->writeln(sprintf('<info>processing product sku %s</info>', $product->getSku()));
            // balance 'Global' && 'Virtual' Warehouses
            $this->inventoryBalancer->balanceInventory($product, false, true);

            // balance 'Fixed' warehouses
            $this->inventoryBalancer->balanceInventory($product, true, true);
        }
    }
}
