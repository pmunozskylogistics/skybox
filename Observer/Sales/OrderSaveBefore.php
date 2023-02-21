<?php
namespace Skybox\Checkout\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;

class OrderSaveBefore implements ObserverInterface {
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $order = $observer->getOrder();
        $order->setData('skybox_fee', 15);
    }
}
