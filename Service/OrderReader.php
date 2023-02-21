<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Service;

use Skybox\Checkout\Api\OrderReaderInterface;

class OrderReader implements OrderReaderInterface
{
    public function __construct(
        \Magento\Sales\Model\Order $order
    ) {
        $this->order = $order;
    }

    public function realOrderId($orderId) {
        $order = $this->order->load($orderId);
        return $order->getRealOrderId();
    }
}

