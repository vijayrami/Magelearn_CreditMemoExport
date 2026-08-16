<?php

namespace Magelearn\CreditMemoExport\Model;

use Magento\Framework\Exception\AlreadyExistsException;
use Magelearn\CreditMemoExport\Api\CreditmemoExportInterface;

class CreditmemoExportRepository
{
    /**
     * @var CreditmemoExportFactory
     */
    private $creditmemoExportFactory;

    /**
     * @var ResourceModel\CreditmemoExport
     */
    private $creditmemoExportResource;

    private $cache = [];

    public function __construct(
        CreditmemoExportFactory $creditmemoExportFactory,
        ResourceModel\CreditmemoExport $creditmemoExportResource
    ) {
        $this->creditmemoExportFactory = $creditmemoExportFactory;
        $this->creditmemoExportResource = $creditmemoExportResource;
    }

    public function findByCreditmemoEntityId($creditmemoEntityId): CreditmemoExportInterface
    {
        if (!array_key_exists($creditmemoEntityId, $this->cache)) {
            /** @var CreditmemoExport $object */
            $object = $this->creditmemoExportFactory->create();
            $this->creditmemoExportResource->load($object, $creditmemoEntityId, 'cm_entity_id');

            if ($object->isObjectNew()) {
                $object->setCreditmemoEntityId($creditmemoEntityId);
                $object->setDataChanges(false);
            }

            $this->cache[$creditmemoEntityId] = $object;
        }

        return $this->cache[$creditmemoEntityId];
    }

    /**
     * @param CreditmemoExport $creditmemoExport
     * @throws AlreadyExistsException
     */
    public function save(CreditmemoExportInterface $creditmemoExport)
    {
        if (!$creditmemoExport->hasDataChanges()) {
            return;
        }

        $this->creditmemoExportResource->save($creditmemoExport);
        unset($this->cache[$creditmemoExport->getCreditmemoEntityId()]);
    }
}
