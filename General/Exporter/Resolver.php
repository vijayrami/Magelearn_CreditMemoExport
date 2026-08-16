<?php

declare(strict_types=1);

namespace Magelearn\CreditMemoExport\General\Exporter;

use Magento\Sales\Model\Order\Creditmemo;
use Magelearn\CreditMemoExport\Api\ExporterStrategyInterface;

class Resolver
{
    public function __construct(
        private readonly File $fileExporter
    ) {
    }

    /**
     * @param Creditmemo $entity
     */
    public function resolve($entity): ExporterStrategyInterface
    {
        return $this->fileExporter;
    }
}
