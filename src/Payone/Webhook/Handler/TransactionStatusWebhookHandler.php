<?php

declare(strict_types=1);

namespace PayonePayment\Payone\Webhook\Handler;

use PayonePayment\Components\DataHandler\Transaction\TransactionDataHandlerInterface;
use PayonePayment\Components\TransactionStatus\TransactionStatusServiceInterface;
use PayonePayment\Struct\PaymentTransaction;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class TransactionStatusWebhookHandler implements WebhookHandlerInterface
{
    /** @var TransactionStatusServiceInterface */
    private $transactionStatusService;

    /** @var TransactionDataHandlerInterface */
    private $transactionDataHandler;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        TransactionStatusServiceInterface $transactionStatusService,
        TransactionDataHandlerInterface $transactionDataHandler,
        LoggerInterface $logger
    ) {
        $this->transactionStatusService = $transactionStatusService;
        $this->transactionDataHandler   = $transactionDataHandler;
        $this->logger                   = $logger;
    }

    public function supports(SalesChannelContext $salesChannelContext, array $data): bool
    {
        if (array_key_exists('txaction', $data)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function process(SalesChannelContext $salesChannelContext, Request $request): void
    {
        $data = $request->request->all();

        /** @var null|PaymentTransaction $paymentTransaction */
        $paymentTransaction = $this->transactionDataHandler->getPaymentTransactionByPayoneTransactionId(
            $salesChannelContext->getContext(),
            (int) $data['txid']
        );

        if (null === $paymentTransaction) {
            $this->logger->warning(sprintf('Could not get transaction for id %s', (int) $data['txid']));

            return;
        }

        // Sanitize incoming data
        $data = $this->utf8EncodeRecursive($data);

        $payoneTransactionData = $this->transactionDataHandler->getTransactionDataFromWebhook($paymentTransaction, $data);

        $this->transactionDataHandler->saveTransactionData($paymentTransaction, $salesChannelContext->getContext(), $payoneTransactionData);
        $this->transactionStatusService->transitionByConfigMapping($salesChannelContext, $paymentTransaction, $data);
    }

    private function utf8EncodeRecursive(array $transactionData): array
    {
        foreach ($transactionData as &$transactionValue) {
            if (is_array($transactionValue)) {
                $transactionValue = $this->utf8EncodeRecursive($transactionValue);

                continue;
            }

            $transactionValue = utf8_encode($transactionValue);
        }
        unset($transactionValue);

        return $transactionData;
    }
}
