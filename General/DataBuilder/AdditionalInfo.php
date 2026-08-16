<?php

declare(strict_types=1);

namespace Magelearn\CreditMemoExport\General\DataBuilder;

use Magento\Sales\Model\Order\Creditmemo;
use Magelearn\CreditMemoExport\Api\GeneralDataBuilderInterface;

class AdditionalInfo implements GeneralDataBuilderInterface
{
    /**
     * @param Creditmemo $entity
     * @return array<string, array<string, mixed>>
     */
    public function build($entity): array
    {
        return [
            'additionalInfo' => [
                'createdAt' => $entity->getCreatedAt(),
                'currency' => $entity->getOrderCurrencyCode(),
            ],
        ];
    }
}
