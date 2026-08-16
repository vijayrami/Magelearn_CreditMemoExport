<?php

namespace Magelearn\CreditMemoExport\Api;

interface CreditmemoExportInterface extends ExportInterface
{
    public function setCreditmemoEntityId(int $creditmemoEntityId): CreditmemoExportInterface;
}
