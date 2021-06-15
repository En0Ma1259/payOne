<?php

declare(strict_types=1);

namespace PayonePayment\Payone\RequestParameter\Builder\Debit;

use PayonePayment\PaymentHandler\PayoneDebitPaymentHandler;
use PayonePayment\Payone\RequestParameter\Builder\AbstractRequestParameterBuilder;
use PayonePayment\Payone\RequestParameter\Struct\PaymentTransactionStruct;
use Shopware\Core\Framework\Struct\Struct;

class AuthorizeRequestParameterBuilder extends AbstractRequestParameterBuilder
{
    /** @param PaymentTransactionStruct $arguments */
    public function getRequestParameter(
        Struct $arguments
    ): array {
        return [
            'clearingtype'      => 'elv',
            'request'           => 'authorization',
            'iban'              => $arguments->getRequestData()->get('iban', ''),
            'bic'               => $arguments->getRequestData()->get('bic', ''),
            'bankaccountholder' => $arguments->getRequestData()->get('accountOwner', ''),
        ];
    }

    /** @param PaymentTransactionStruct $arguments */
    public function supports(Struct $arguments): bool
    {
        if (!($arguments instanceof PaymentTransactionStruct)) {
            return false;
        }

        $paymentMethod = $arguments->getPaymentMethod();
        $action        = $arguments->getAction();

        return $paymentMethod === PayoneDebitPaymentHandler::class && $action === self::REQUEST_ACTION_AUTHORIZE;
    }
}
