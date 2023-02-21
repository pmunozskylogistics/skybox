<?php
namespace Skybox\Checkout\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;

class OrderLoadAfter implements ObserverInterface {
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $this->logger->debug('[SBC] OrderLoadAfter::execute - Begin');
        $order = $observer->getOrder();
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->getOrderExtensionDependency();
        }
        $attr = $order->getData('skybox_fee');
	$this->logger->debug('Get Data -> ' . $attr);
        $extensionAttributes->setSkbcheckoutFee($attr);
        $order->setExtensionAttributes($extensionAttributes);
    }

    private function getOrderExtensionDependency() {
        $orderExtension = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Sales\Api\Data\OrderExtension');
        return $orderExtension;
    }
}
