<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Plugin\Product;

use \Magento\Catalog\Pricing\Price\ConfiguredPrice;

/**
 * Class AfterPrice
 * @package Skybox\Checkout\Plugin\Product
 * @see \Magento\Framework\Pricing\Render
 */
class AfterPrice
{
    const AFTERPRICE_PRODUCT_KEY = "skb_afterprice_product";
    const ONLY_ONCE_AFTERPRICE = "skb_afterprice_once";

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    private $integrationType;

    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Skybox\Checkout\Helpers\Data $dataHelper
    ) {
        $this->layout = $layout;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->integrationType = $dataHelper->getIntegrationType();        
    }

    public function beforeRender(
        \Magento\Framework\Pricing\Render $subject,
        $priceCode,
        \Magento\Catalog\Model\Product $saleableItem,
        array $arguments = []
    ) {
        if($this->integrationType==1){
            $this->registry->unregister(self::AFTERPRICE_PRODUCT_KEY);
            $this->registry->register(self::AFTERPRICE_PRODUCT_KEY, $saleableItem);
        }
    }

    public function afterRender(\Magento\Framework\Pricing\Render $subject, $renderHtml) {
        if($this->integrationType==1){
        
            if (strlen($renderHtml) <= 1)
                return $renderHtml;

            try {
                $product = $this->registry->registry(self::AFTERPRICE_PRODUCT_KEY);

                if ($product) {
                    $priceBlock = $this->layout->createBlock(
                        '\Skybox\Checkout\Block\Product\AfterPrice',
                        'skb_afterprice_block_' . $product->getId(), 
                        ['product' => $product]
                    );
                    $priceBlock->setTemplate('Skybox_Checkout::product/afterprice.phtml');
                    $priceHtml = $priceBlock->toHtml();
                    return $renderHtml . $priceHtml;
                }
            }
            catch(\Exception $ex){
                $this->logger->debug("[SBC] AfterPrice::afterRender Error => " . json_encode($ex->getMessage()));
            }
        }

        return $renderHtml;
    }
}
