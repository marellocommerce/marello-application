<?php

namespace Marello\Bundle\InventoryBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceInventoryTopic;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancer;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InventoryBalanceCommand extends Command
{
    use JobIdGenerationTrait;

    const NAME = 'marello:inventory:balance';
    const ALL = 'all';
    const PRODUCT = 'product';
    const EXECUTION_TYPE = 'execution-type';
    const BACKGROUND = 'background';
    const DIRECT = 'direct';

    /** @var string */
    private $executionType;

    public function __construct(
        protected ManagerRegistry $registry,
        protected InventoryBalancer $inventoryBalancer,
        protected MessageProducerInterface $messageProducer
    ) {
        parent::__construct();
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
                    'Process balancing either in the background or directly (possible values: %s, %s)',
                    self::BACKGROUND,
                    self::DIRECT
                ),
                self::BACKGROUND
            )
            ->addOption(
                self::ALL,
                null,
                null,
                'Balance all Products'
            )
            ->addArgument(
                self::PRODUCT,
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Product sku(s) for balancing inventory'
            )
            ->setDescription('Balance inventory for Product(s)');
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
        } elseif (!empty($input->getArgument(self::PRODUCT))) {
            $this->processProducts($input, $output);
        } else {
            $output->writeln(
                '<comment>ATTENTION</comment>: To update all products run command with <info>--all</info> option:'
            );
            $output->writeln(sprintf('<info>%s --all</info>', $this->getName()));
        }

        return 0;
    }

    /**
     * Process all products for balancing
     * @param OutputInterface $output
     */
    protected function processAllProducts(OutputInterface $output)
    {
        $output->writeln('<info>Start processing all Products for balancing</info>');

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
        $output->writeln('<info>Start processing Products for balancing</info>');
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
        $products = $input->getArgument(self::PRODUCT);
        /** @var ProductRepository $productRepository */
        $productRepository = $this->registry
            ->getManagerForClass(Product::class)
            ->getRepository(Product::class);

        if (count($products) > 0) {
            $products = $productRepository->findBy(['sku' => $products]);
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
            if ($this->executionType === self::DIRECT) {
                $output->writeln(sprintf('<info>processing product with sku %s</info>', $product->getSku()));
                // balance 'Global' && 'Virtual' Warehouses
                $this->inventoryBalancer->balanceInventory($product, false, true);

                // balance 'Fixed' warehouses
                $this->inventoryBalancer->balanceInventory($product, true, true);
            } else {
                $id = $product->getId();
                $jobId = $this->generateJobId($id);
                $this->messageProducer->send(
                    ResolveRebalanceInventoryTopic::getName(),
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
