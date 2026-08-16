<?php

declare(strict_types=1);

namespace Magelearn\CreditMemoExport\Api;

use Magento\Sales\Model\Order\Creditmemo;

interface GeneralDataBuilderInterface
{
    /**
     * @param Creditmemo $entity
     * @return array<string, array<string, mixed>>
     */
    public function build($entity): array;
}
