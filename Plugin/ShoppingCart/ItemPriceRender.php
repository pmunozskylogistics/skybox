<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Plugin\ShoppingCart;

/**
 * Class AfterPrice
 * @package Skybox\Checkout\Plugin\ShoppingCart
 * @see \Magento\Tax\Block\Item\Price\Renderer
 */
class ItemPriceRender
{
    private $integrationType;

    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Psr\Log\LoggerInterface $logger,
        \Skybox\Checkout\Helpers\Data $dataHelper
    ) {
        $this->layout = $layout;
        $this->logger = $logger;
        $this->integrationType = $dataHelper->getIntegrationType();
    }

    public function afterFormatPrice(\Magento\Tax\Block\Item\Price\Renderer $subject, $price)
    {        
        if($this->integrationType==1){            
            $product = $subject->getItem()->getProduct();
            $productId = $subject->getItem()->getId();

            if($productId === false){
                $productId = $subject->getItem()->getProductId();
            }

            $htmlPrice = '';
            try {
                $itemPriceBlock = $this->layout->getBlock('skb_itempricerender_' . $productId);
                if ($itemPriceBlock === false) {
                    $itemPriceBlock = $this->layout->createBlock(
                        '\Skybox\Checkout\Block\Cart\ItemPrice',
                        'skb_itempricerender_' . $productId,
                        []
                    );
                    $itemPriceBlock->setProductId($productId);
                    $itemPriceBlock->setTemplate('Skybox_Checkout::cart/itemprice.phtml');
                }
                $htmlPrice =  $itemPriceBlock->toHtml(); 
                $price = str_replace("price", "price skbx-price-store", $price);
            }
            catch(\Exception $ex) {
                $this->logger->debug("[SBC] ItemPriceRender::afterFormatPrice Error => " . json_encode($ex->getMessage()));
            }
            return $price . $htmlPrice;
        }
        return $price;
    }
}
