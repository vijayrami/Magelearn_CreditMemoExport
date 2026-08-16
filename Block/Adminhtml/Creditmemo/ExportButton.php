<?php

declare(strict_types=1);

namespace Magelearn\CreditMemoExport\Block\Adminhtml\Creditmemo;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Registry;

class ExportButton extends Template
{
    private const EXPORT_ROUTE = 'creditmemoexport/creditmemo/export';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        private readonly Registry $coreRegistry,
        private readonly UrlInterface $backendUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getExportUrl(): ?string
    {
        $creditmemo = $this->coreRegistry->registry('current_creditmemo');

        if ($creditmemo === null) {
            return null;
        }

        return $this->backendUrl->getUrl(
            self::EXPORT_ROUTE,
            [
                'creditmemo_id' => (int) $creditmemo->getEntityId(),
            ]
        );
    }
}
