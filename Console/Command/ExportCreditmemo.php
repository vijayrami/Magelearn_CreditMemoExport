<?php

namespace Magelearn\CreditMemoExport\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magelearn\CreditMemoExport\Creditmemo\Consumer;

class ExportCreditmemo extends Command
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        State $state,
        Consumer $consumer,
        CreditmemoRepositoryInterface $creditmemoRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->state = $state;
        $this->consumer = $consumer;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;        
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('magelearn:export:creditmemo');
        $this->setDescription('Export Credit Memo to M3');
        $this->addArgument('increment-id', InputArgument::OPTIONAL, 'Increment Id');
        $this->addOption('store-id', null, InputOption::VALUE_REQUIRED, 'Store Id');
        $this->addOption('requested-from', null, InputOption::VALUE_REQUIRED, 'Requested from date');
        $this->addOption('requested-to', null, InputOption::VALUE_REQUIRED, 'Requested to date');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show only order numbers, do not export');

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);

            $incrementId = $input->getArgument('increment-id');
            $storeId = $input->getOption('store-id');
            $requestedFrom = $input->getOption('requested-from');
            $requestedTo = $input->getOption('requested-to');
            $dryRun = $input->getOption('dry-run');

            if (!$incrementId && !$requestedFrom && !$requestedTo) {
                throw new LocalizedException(
                    __('You need to specify either increment-id, requested-from or requested-to')
                );
            }

            $searchCriteria = $this->searchCriteriaBuilder->build(
                $incrementId,
                $storeId,
                $requestedFrom,
                $requestedTo
            );

            foreach ($this->creditmemoRepository->getList($searchCriteria)->getItems() as $creditmemo) {

                $output->writeln($creditmemo->getIncrementId());

                if ($dryRun) {
                    continue;
                }

                $this->consumer->process($creditmemo->getEntityId());
            }

            $output->writeln('<info>[ OK ]</info>');
        } catch (\Throwable $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
