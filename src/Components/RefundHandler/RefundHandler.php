<?php

declare(strict_types=1);

namespace PayonePayment\Components\RefundHandler;

use PayonePayment\Payone\Client\PayoneClientInterface;
use PayonePayment\Payone\Request\Refund\RefundRequest;
use PayonePayment\Payone\Request\RequestFactory;
use PayonePayment\Payone\Struct\PaymentTransactionStruct;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Framework\Context;

class RefundHandler implements RefundHandlerInterface
{
    /** @var RequestFactory */
    private $requestFactory;

    /** @var PayoneClientInterface */
    private $client;

    public function __construct(RequestFactory $requestFactory, PayoneClientInterface $client)
    {
        $this->requestFactory = $requestFactory;
        $this->client         = $client;
    }

    public function refundTransaction(OrderTransactionEntity $transaction, Context $context): bool
    {
        $paymentTransaction = PaymentTransactionStruct::fromOrderTransaction($transaction);

        $request = $this->requestFactory->generateRequest(
            $paymentTransaction,
            $context,
            RefundRequest::class
        );

        $response = $this->client->request($request);

        var_dump($response);

        if (empty($response['Status']) && $response['Status'] !== 'REDIRECT') {
            // TODO: Error Handling

            return false;
        }

        return true;
    }
}
