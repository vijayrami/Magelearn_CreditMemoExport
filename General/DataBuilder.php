<?php

declare(strict_types=1);

namespace Magelearn\CreditMemoExport\General;

use Magento\Sales\Model\Order\Creditmemo;
use Magelearn\CreditMemoExport\Api\GeneralDataBuilderInterface;

class DataBuilder implements GeneralDataBuilderInterface
{
    /**
     * @var GeneralDataBuilderInterface[]
     */
    private array $dataBuilders;

    /**
     * @param GeneralDataBuilderInterface[] $dataBuilders
     */
    public function __construct(
        array $dataBuilders
    ) {
        foreach ($dataBuilders as $name => $dataBuilder) {
            if (!$dataBuilder instanceof GeneralDataBuilderInterface) {
                throw new \TypeError(sprintf(
                    "DataBuilder with name %s must implement %s, instead it's %s",
                    $name,
                    GeneralDataBuilderInterface::class,
                    get_class($dataBuilder)
                ));
            }
        }

        $this->dataBuilders = $dataBuilders;
    }

    /**
     * @param Creditmemo $entity
     * @return array<string, array<string, mixed>>
     */
    public function build($entity): array
    {
        $data = [];
        foreach ($this->dataBuilders as $dataBuilder) {
            $data = array_merge(
                $data,
                $dataBuilder->build($entity)
            );
        }

        return $data;
    }
}
