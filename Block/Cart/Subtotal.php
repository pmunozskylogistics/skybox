<?php

namespace Skybox\Checkout\Block\Cart;

class Subtotal extends \Magento\Framework\View\Element\Template {

	private $integrationType;

    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Skybox\Checkout\Helpers\Config $configHelper,
        \Skybox\Checkout\Helpers\Data $dataHelper
	) {
		parent::__construct($context);
		$this->configHelper = $configHelper;
		$this->integrationType = $dataHelper->getIntegrationType();
	}

	public function isEnabled() {
		return $this->configHelper->isEnabled();
	}

	public function getIntegrationType(){
		return $this->integrationType;
	}
}
