<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Controller\Cart;

class Delete extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session\Proxy $session,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->messageManager = $context->getMessageManager();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->session = $session;
        $this->logger = $logger;
    }

    public function execute() {
        try{
            $textIds = $this->getRequest()->getParam('idVariant');
            $variantIds = json_decode("$textIds");
            $cartItems = $this->session->getQuote()->getAllVisibleItems();
            $cartTmp = $this->session->getQuote();
            foreach($variantIds as $item) {
                $quoteItem = $this->inArray($item->idVariant, $cartItems);
                if ($quoteItem !== null) {
                    if ($this->isRestricted($item))
                        $this->messageManager->addErrorMessage("'" . $quoteItem->getName() . "' not available in your country");
                    else
                        $this->messageManager->addErrorMessage("Can't '" . $quoteItem->getName() . "' add to cart");

                    $this->cart->removeItem($quoteItem->getItemId());
                }
            }
            $this->cart->save();

            return $this->resultJson(["success" => true]);
        } catch(\Exception $ex) {
            $this->logger->debug("[SBC] Index::execute Error => " . $ex->getMessage());
            return $this->resultJson(["success" => false]);
        }
    }

    private function inArray($productId, $allItems) {
        foreach ($allItems as $item) {
            $prodId = $item->getProduct()->getIdBySku($item->getSku());
            if ($prodId == $productId) {
                return $item;
            }
        }
        return null;
    }

    private function isRestricted($item) {
        foreach($item->Errors as $err) {
            if ($err->Code == 50032)
                return true;
        }
        return false;
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
