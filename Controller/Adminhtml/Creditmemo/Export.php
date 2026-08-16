<?php

declare(strict_types=1);

namespace Magelearn\CreditMemoExport\Controller\Adminhtml\Creditmemo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo\CommentRepository;
use Psr\Log\LoggerInterface;
use Magelearn\CreditMemoExport\General\ExportConfiguration;

use function __;

class Export extends Action
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magelearn_CreditMemoExport::export_creditmemo';

    public const CREDITMEMO_VIEW_PATH = 'sales/order_creditmemo/view';

    public function __construct(
        private readonly Session $authSession,
        private readonly RedirectFactory $redirectFactory,
        private readonly PublisherInterface $publisher,
        private readonly CreditmemoRepositoryInterface $creditmemoRepository,
        private readonly CommentRepository $commentRepository,
        private readonly ExportConfiguration $config,
        private readonly LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $resultRedirect = $this->redirectFactory->create();
        $creditmemoId = (int) $this->getRequest()->getParam('creditmemo_id');

        try {
            if ($creditmemoId <= 0) {
                throw new LocalizedException(
                    __('A valid Credit Memo ID is required.')
                );
            }

            $this->exportCreditmemo($creditmemoId);

            $this->messageManager->addSuccessMessage(
                __('Credit Memo has been queued for export to Service.')
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addNoticeMessage($e->getMessage());
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Credit Memo export to Service failed.'));
            $this->logger->error(
                'Manual Credit Memo export failed.',
                [
                    'creditmemo_id' => $creditmemoId,
                    'exception_class' => $e::class,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
        }

        return $resultRedirect->setPath(self::CREDITMEMO_VIEW_PATH, ['creditmemo_id' => $creditmemoId]);
    }

    private function exportCreditmemo(int $creditmemoId): void
    {
        $creditmemo = $this->creditmemoRepository->get($creditmemoId);
        $storeId = (int) $creditmemo->getStoreId();

        if (!$this->config->getIsEnabled($storeId)) {
            throw new LocalizedException(
                __('Service export is disabled for this store.')
            );
        }

        $username = $this->getAdminUserName();

        $this->publisher->publish($this->config->getTopicName(), (string) $creditmemoId);

        $comment = $creditmemo->addComment(
            __(
                'Credit Memo manually queued for export to Service by %1.',
                $username
            )
        );

        $this->commentRepository->save($comment);

        $this->logger->info(
            'Credit Memo manually queued for Service export.',
            [
                'creditmemo_id' => $creditmemoId,
                'user' => $username,
            ]
        );
    }

    private function getAdminUserName(): string
    {
        $user = $this->authSession->getUser();

        return $user ? $user->getName() : 'System';
    }
}
