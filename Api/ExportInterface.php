<?php

namespace Magelearn\CreditMemoExport\Api;

interface ExportInterface
{
    /**
     * @return int|null
     */
    public function getCreditmemoEntityId(): ?int;

    public function setCreditmemoEntityId(int $creditmemoEntityId): self;

    /**
     * @return string|null
     */
    public function getExportedAt(): ?string;

    /**
     * @return string|null
     */
    public function getPublishedAt(): ?string;

    public function publish(): void;

    public function export(): void;
}
