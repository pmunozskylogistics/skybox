<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AjaxSearch implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Skybox\Checkout\Helpers\Config
     */
    private $configHelper;

    /**
     * Constructor.
     *
     * @param \Magento\Catalog\Model\Session\Proxy $checkoutSession
     * @param \Skybox\Checkout\Helper\Data $dataHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Catalog\Model\Session\Proxy $checkoutSession,
        \Skybox\Checkout\Helpers\Config $configHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->configHelper      = $configHelper;
        $this->_logger         = $logger;
    }

    /**
     * Disable Payments methods
     *
     * @param EventObserver $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
	var_dump($observer);
        if ($this->configHelper->isEnabled()) {
            /** @var \Magento\Framework\DataObject $result */
 //           $result = $observer->getEvent()->getResult();
//            $result->setData('is_available', false);i
        }
    }
}
