<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Controller\Cart;

class Clear extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session\Proxy $session,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->session = $session;
        $this->logger = $logger;
    }

    public function execute() {
        try {
            $cart = $this->session->getQuote();
            $items = $this->session->getQuote()->getAllVisibleItems();
            foreach($items as $item) {
                $this->cart->removeItem($item->getItemId())->save();
            }
            return $this->resultJson(["success" => true]);
        }
        catch(\Exception $ex) {
            $this->logger->debug("[SBC] Index::execute Error => " . $ex->getMessage());
            return $this->resultJson(["success" => false]);
        }
    }

    private function resultJson($data)
    {
        $result = $this->resultJsonFactory->create();
        $result->setHeader('Content-type', 'aplication/json; charset=UTF-8');
        $result->setHttpResponseCode(\Magento\Framework\Webapi\Response::HTTP_OK);
        $result->setData($data);

        return $result;
    }    
}
