<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Controller\Cart;

class Update extends \Magento\Framework\App\Action\Action
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
        $success = false;
        $msg = "";
        try {
            $idVariant = $this->getRequest()->getParam('idVariant');
            $qty = $this->getRequest()->getParam('qty');
            $cart = $this->session->getQuote();
            $cartItems = $this->session->getQuote()->getAllVisibleItems();


            foreach ($cartItems as $item) {
                $msg .= '-' . print_r($item->getItemId(),1);
                if($item->getItemId() == $idVariant){
                    $item->setQty((double) $qty);
                    $item->save();
                    $success = true;
                }else{
                    $msg .= 'Not found';
                }
                $cart->save();
            }
            
            return $this->resultJson([
                "success" => $success,
                "msg" => $msg,
                "idVariant" => $idVariant,
                "qty" => $qty
            ]);
        }
        catch(\Exception $ex) {
            $this->logger->debug("[SBC] Index::execute Error => " . $ex->getMessage());
            $msg = $ex->getMessage();
            $success = false;
            return $this->resultJson([
                "success" => $success,
                "msg" => $msg
            ]);
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
