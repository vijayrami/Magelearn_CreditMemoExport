<?php

declare(strict_types=1);

namespace Magelearn\CreditMemoExport\General;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Sales\Model\Order\Creditmemo;
use Magelearn\CreditMemoExport\General\Exporter\Resolver;
use Magelearn\CreditMemoExport\Xml\Writer;

class Exporter
{
    public function __construct(
        private readonly DataBuilder $dataBuilder,
        private readonly Resolver $exporterResolver,
        private readonly Writer $xmlWriter
    ) {
    }

    /**
     * @param Creditmemo $entity
     * @throws ConnectionLostException
     * @throws LocalizedException
     */
    public function process($entity): void
    {
        try {
            $xml = $this->buildXmlString($entity);
            $exporter = $this->exporterResolver->resolve($entity);
            $exporter->export($entity, $xml);
        } catch (\Throwable $e) {
            // ConnectionLostException will force queue framework to retry
            throw new ConnectionLostException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param Creditmemo $entity
     * @throws LocalizedException
     * @throws InputException
     */
    private function buildXmlString($entity): string
    {
        $xml = $this->xmlWriter->toXml($this->dataBuilder->build($entity));

        return $xml->saveXML();
    }
}
