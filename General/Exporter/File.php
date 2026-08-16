<?php

declare(strict_types=1);

namespace Magelearn\CreditMemoExport\General\Exporter;

use Magento\Framework\Filesystem;
use Magento\Sales\Model\Order\Creditmemo;
use Magelearn\CreditMemoExport\Api\ExporterStrategyInterface;
use Magelearn\CreditMemoExport\General\ExportConfiguration;

class File implements ExporterStrategyInterface
{
    public function __construct(
        private readonly ExportConfiguration $exportConfiguration,
        private readonly Filesystem $filesystem
    ) {
    }

    /**
     * @param Creditmemo $entity
     */
    public function export($entity, string $xml): void
    {
        $folder = $this->filesystem->getDirectoryWrite('base');
        $filename = $this->generateFilename($entity);
        $folder->writeFile($filename, $xml);
        $folder->copyFile($filename, $this->generateFilename($entity, true));
    }

    /**
     * @param Creditmemo $entity
     */
    private function generateFilename($entity, bool $backup = false): string
    {
        if ($backup) {
            $basePath = $this->exportConfiguration->getBackupPath()
                . DIRECTORY_SEPARATOR
                . date('Y')
                . DIRECTORY_SEPARATOR
                . date('m');
        } else {
            $basePath = $this->exportConfiguration->getExportPath();
        }

        return $basePath
            . DIRECTORY_SEPARATOR
            . sprintf('%s.xml', $entity->getIncrementId());
    }
}
