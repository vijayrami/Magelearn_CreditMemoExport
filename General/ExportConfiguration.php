<?php

namespace Magelearn\CreditMemoExport\General;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ExportConfiguration
{
    private const XML_PATH_EXPORT_PREFIX = 'magelearn_export/';
    private const XML_PATH_EXPORT_ENABLED = '/enabled';
    private const XML_PATH_EXPORT_FOLDER = '/export_path';
    private const XML_PATH_BACKUP_FOLDER = '/backup_path';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $topicName;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        string $entityName,
        string $topicName
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->entityName = $entityName;
        $this->topicName = $topicName;
    }

    public function getIsEnabled(int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EXPORT_PREFIX . $this->entityName . self::XML_PATH_EXPORT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getExportPath(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EXPORT_PREFIX . $this->entityName . self::XML_PATH_EXPORT_FOLDER
        );
    }

    public function getBackupPath(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EXPORT_PREFIX . $this->entityName . self::XML_PATH_BACKUP_FOLDER
        );
    }

    public function getTopicName(): ?string
    {
        return $this->topicName;
    }
}
