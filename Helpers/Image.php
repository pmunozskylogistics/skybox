<?php

namespace Skybox\Checkout\Helpers;

class Image {
    private $_storeManager;
    private $_appEmulation;
    private $_blockFactory;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Store\Model\App\Emulation $appEmulation
    ) {
        $this->_storeManager = $storeManager;
        $this->_blockFactory = $blockFactory;
        $this->_appEmulation = $appEmulation;
    }

    public function getImageUrl($product, $imageType = '')
    {
        $storeId = $this->_storeManager->getStore()->getId();

        $this->_appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        $imageBlock = $this->_blockFactory->createBlock('Magento\Catalog\Block\Product\ListProduct');
        $productImage = $imageBlock->getImage($product, $imageType);
        $imageUrl = $productImage->getImageUrl();

        $this->_appEmulation->stopEnvironmentEmulation();

        return $imageUrl;
    }
}
