<?php

namespace Skybox\Checkout\Block;

class Cart extends \Magento\Framework\View\Element\Template {
	/**
	 * @var \Skybox\Checkout\Helpers\Config
	 */
	private $configHelper;

	private $integrationType;

    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Skybox\Checkout\Helpers\Config $configHelper,
        \Skybox\Checkout\Helpers\Data $dataHelper
	) {
		parent::__construct($context);
		$this->storeManager = $context->getStoreManager();
		$this->configHelper = $configHelper;
		$this->integrationType = $dataHelper->getIntegrationType();
	}

	public function getBaseUrl() {
		return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
	}

	public function isEnabled() {
		return $this->configHelper->isEnabled();
	}

	public function getConfig() {
		return $this->configHelper;
	}

	public function getIntegrationType(){
		return $this->integrationType;
	}
}
