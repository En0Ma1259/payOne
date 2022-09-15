<?php

declare(strict_types=1);

namespace PayonePayment\Components\CartHasher;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface CartHasherInterface
{
    /**
     * @param Cart|OrderEntity $entity
     */
    public function generate(Struct $entity, SalesChannelContext $context): string;

    /**
     * @param Cart|OrderEntity $entity
     */
    public function validate(Struct $entity, string $cartHash, SalesChannelContext $context): bool;

    /**
     * returns Criteria-Objects with all dependencies which are required to generate the full hash for an order
     */
    public function getCriteriaForOrder(string $orderId = null): Criteria;
}
