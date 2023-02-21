<?php

namespace Skybox\Checkout\Helpers;

class Config extends \Magento\Framework\App\Helper\AbstractHelper {
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
     ) {
         parent::__construct($context);
         $this->storeManager = $storeManager;
    }

    public function isEnabled() {
        return $this->scopeConfig->getValue(
            'skyboxcheckout/settings/skb_enable_frontend', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMerchantId() {
        return $this->scopeConfig->getValue(
            'skyboxcheckout/settings/skb_merchant_id', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMerchantCode() {
        return $this->scopeConfig->getValue(
            'skyboxcheckout/settings/skb_merchant_code', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMerchantKey() {
        return $this->scopeConfig->getValue(
            'skyboxcheckout/settings/skb_merchant_key', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getWeigthUnit() {
        return $this->scopeConfig->getValue(
            'skyboxcheckout/settings/skb_weight_unit', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getApiUrl() {
        return $this->scopeConfig->getValue(
            'skyboxcheckout/settings/skb_url_api', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getClientUrl() {
        return $this->scopeConfig->getValue(
            'skyboxcheckout/settings/skb_url_client', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
