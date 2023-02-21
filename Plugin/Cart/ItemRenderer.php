<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Plugin\Cart;

/**
 * Class ItemRendererPlugin
 * @package Skybox\Checkout\Plugin\Cart
 * @see \Magento\Checkout\Block\Cart\Item\Renderer
 */
class ItemRenderer
{
    private $logger;

    private $integrationType;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Skybox\Checkout\Helpers\Data $dataHelper       
    ) {
        $this->logger = $logger;
        $this->integrationType = $dataHelper->getIntegrationType();
    }

    public function afterGetRowTotalHtml(\Magento\Checkout\Block\Cart\Item\Renderer $item, $result){
        
        $result = str_replace('sky--Price-', 'sky--Total-', $result);
        
        return $result;
    }
}
