<?php

namespace Marello\Bundle\InventoryBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Async\Topics;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancer;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InventoryBalanceCommand extends Command
{
    const NAME = 'marello:inventory:rebalance';
    const ALL = 'all';
    const PRODUCT = 'product';
    const EXECUTION_TYPE = 'execution-type';
    const BACKGROUND = 'background';
    const IMMEDIATELY = 'immediately';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var InventoryBalancer
     */
    private $inventoryBalancer;

    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;
    
    private $executionType;
    
    public function __construct(
        Registry $registry,
        InventoryBalancer $inventoryBalancer,
        MessageProducerInterface $messageProducer
    ) {
        parent::__construct();
        
        $this->registry = $registry;
        $this->inventoryBalancer = $inventoryBalancer;
        $this->messageProducer = $messageProducer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->addOption(
                self::EXECUTION_TYPE,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'for selecting execution type (possible values: %s, %s)',
                    self::BACKGROUND,
                    self::IMMEDIATELY
                ),
                self::BACKGROUND
            )
            ->addOption(
                self::ALL,
                null,
                null,
                'for all products inventory rebalancing'
            )
            ->addOption(
                self::PRODUCT,
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
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
        $this->executionType = $input->getOption(self::EXECUTION_TYPE);
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
            if ($this->executionType === self::IMMEDIATELY) {
                $output->writeln(sprintf('<info>processing product with sku %s</info>', $product->getSku()));
                // balance 'Global' && 'Virtual' Warehouses
                $this->inventoryBalancer->balanceInventory($product, false, true);

                // balance 'Fixed' warehouses
                $this->inventoryBalancer->balanceInventory($product, true, true);
            } else {
                $id = $product->getId();
                $jobId = md5($id);
                $this->messageProducer->send(
                    Topics::RESOLVE_REBALANCE_INVENTORY,
                    ['product_id' => $id, 'jobId' => $jobId]
                );
                $output->writeln(
                    sprintf(
                        '<info>job #%s was created for processing product with sku %s</info>',
                        $jobId,
                        $product->getSku()
                    )
                );
            }
        }
    }
}
