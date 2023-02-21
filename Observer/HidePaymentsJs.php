<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class HidePayments implements ObserverInterface
{

    /**
     * @var \Skybox\Checkout\Helpers\Config
     */
    private $configHelper;

    public function __construct(
        \Skybox\Checkout\Helpers\Config $configHelper,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->configHelper      = $configHelper;
        $this->_logger         = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
	{
        $this->_logger->debug("?");
        if ($this->configHelper->isEnabled()) {
            $this->_logger->debug(print_r($observer,1));
        }
    }
}