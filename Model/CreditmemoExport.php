<?php

declare(strict_types=1);

namespace Magelearn\CreditMemoExport\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magelearn\CreditMemoExport\Api\CreditmemoExportInterface;
use Magelearn\CreditMemoExport\Model\ResourceModel\CreditmemoExport as CreditmemoExportResource;

class CreditmemoExport extends AbstractModel implements CreditmemoExportInterface
{
    private DateTime $dateTime;

    /**
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->dateTime = $dateTime;
    }

    protected function _construct(): void
    {
        $this->_init(CreditmemoExportResource::class);
        parent::_construct();
    }

    public function getCreditmemoEntityId(): ?int
    {
        return (int) $this->getData('cm_entity_id') ?: null;
    }

    public function setCreditmemoEntityId(int $creditmemoEntityId): CreditmemoExportInterface
    {
        return $this->setData('cm_entity_id', $creditmemoEntityId);
    }

    public function getExportedAt(): ?string
    {
        return $this->getData('exported_at');
    }

    public function getPublishedAt(): ?string
    {
        return $this->getData('published_at');
    }

    public function publish(): void
    {
        $this->setData('published_at', $this->dateTime->date());
    }

    public function export(): void
    {
        $this->setData('exported_at', $this->dateTime->date());
    }
}
