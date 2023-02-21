<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 */

namespace Skybox\Checkout\Api;

/**
 * Generate Order Interface
 *
 * @package Skybox\Checkout\Api
 */
interface OrderReaderInterface
{
    /**
    *Get Real Id of Order
    *
    * @param int $orderId
    * @return string
    */
    public function realOrderId($orderId);
}

