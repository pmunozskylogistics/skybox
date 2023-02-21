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
class CustomerData
{
    private $integrationType;

    private $logger;

    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Psr\Log\LoggerInterface $logger,
        \Skybox\Checkout\Helpers\Data $dataHelper
    ) {
        $this->layout = $layout;
        $this->logger = $logger;
        $this->integrationType = $dataHelper->getIntegrationType();
    }

    public function afterAfterGetSectionData($subject, $result)
    {
        $this->logger->debug("afterAfterGetSectionData");
        try {
            $subtotalBlock = $this->layout->createBlock(
                '\Skybox\Checkout\Block\Cart\Subtotal',
                'skb_cart_subtotal',
                []
            );

            $subtotalBlock->setTemplate('Skybox_Checkout::cart/subtotal.phtml');            
            $html = $subtotalBlock->toHtml();

            if($this->integrationType == 1){
                $result['subtotal_excl_tax'] = str_replace("price", "price skbx-price-store", $result['subtotal_excl_tax']);                
            }
            $result['subtotal_excl_tax'] .= $html;
            
        }
        catch(\Exception $ex) {
            $this->logger->debug("[SBC] CustomerData::afterAfterGetSectionData Error => " . json_encode($ex->getMessage()));
        }
        
        return $result;
    }
}
