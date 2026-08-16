<?php

declare(strict_types=1);

namespace Magelearn\CreditMemoExport\Creditmemo;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\CommentRepository;
use Psr\Log\LoggerInterface;
use Magelearn\CreditMemoExport\Exception\CreditmemoExportException;
use Magelearn\CreditMemoExport\General\Exporter;
use Magelearn\CreditMemoExport\Model\CreditmemoExportRepository;

class Consumer
{
    public const SERVICES_CREDITMEMO_EXPORT_COMMENT = 'Creditmemo exported to Services';

    public function __construct(
        private readonly CreditmemoRepositoryInterface $creditmemoRepository,
        private readonly CreditmemoExportRepository $creditmemoExportRepository,
        private readonly CommentRepository $commentRepository,
        private readonly Exporter $exporter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @throws CreditmemoExportException
     */
    public function process(string $creditmemoId): void
    {
        try {
            /** @var Creditmemo $creditmemo */
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);

            $this->exporter->process($creditmemo);

            $this->addComment($creditmemo);
            $this->export($creditmemo);
            $this->logger->info(self::SERVICES_CREDITMEMO_EXPORT_COMMENT, ['CreditmemoId' => $creditmemoId]);
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage(), ['CreditmemoId' => $creditmemoId]);

            throw new CreditmemoExportException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws CouldNotSaveException
     */
    private function addComment(Creditmemo $creditmemo): void
    {
        $comment = $creditmemo->addComment(self::SERVICES_CREDITMEMO_EXPORT_COMMENT);
        $this->commentRepository->save($comment);
    }

    /**
     * @throws AlreadyExistsException
     */
    private function export(Creditmemo $creditmemo): void
    {
        $exportData = $this->creditmemoExportRepository->findByCreditmemoEntityId($creditmemo->getEntityId());
        $exportData->export();
        $this->creditmemoExportRepository->save($exportData);
    }
}
