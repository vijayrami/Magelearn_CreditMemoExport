<?php

declare(strict_types=1);

namespace Magelearn\CreditMemoExport\Api;

use Magento\Sales\Model\Order\Creditmemo;

interface ExporterStrategyInterface
{
    /**
     * @param Creditmemo $entity
     */
    public function export($entity, string $xml): void;
}
