<?php

namespace Skybox\Checkout\Block\Cart;

class ItemPrice extends \Magento\Framework\View\Element\Template {
    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
        \Skybox\Checkout\Helpers\Config $configHelper
	) {
		parent::__construct($context);
        $this->logger       = $context->getLogger();
        $this->configHelper = $configHelper;
    }
    
    private $productId;

	public function isEnabled() {
		return $this->configHelper->isEnabled();
    }
    
    public function setProductId($productId) {
        $this->productId = $productId;
    }

    public function getProductId() {
        return $this->productId;
    }
}
