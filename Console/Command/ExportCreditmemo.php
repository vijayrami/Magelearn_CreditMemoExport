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
use Magelearn\CreditMemoExport\Model\CreditmemoExportRepository;

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

    /**
     * @var CreditmemoExportRepository
     */
    private $creditmemoExportRepository;

    public function __construct(
        State $state,
        Consumer $consumer,
        CreditmemoRepositoryInterface $creditmemoRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CreditmemoExportRepository $creditmemoExportRepository,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->state = $state;
        $this->consumer = $consumer;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->creditmemoExportRepository = $creditmemoExportRepository;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('magelearn:export:creditmemo');
        $this->setDescription('Export Credit Memo to Service');
        $this->addArgument('increment-id', InputArgument::OPTIONAL, 'Increment Id');
        $this->addOption('store-id', null, InputOption::VALUE_REQUIRED, 'Store Id');
        $this->addOption('requested-from', null, InputOption::VALUE_REQUIRED, 'Requested from date');
        $this->addOption('requested-to', null, InputOption::VALUE_REQUIRED, 'Requested to date');
        $this->addOption('not-exported-only', null, InputOption::VALUE_NONE, 'Not exported only');
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
            $notExportedOnly = $input->getOption('not-exported-only');
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

                if ($notExportedOnly) {
                    $exportData = $this->creditmemoExportRepository->findByCreditmemoEntityId(
                        $creditmemo->getEntityId()
                    );

                    if ($exportData->getExportedAt()) {
                        continue;
                    }
                }

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
