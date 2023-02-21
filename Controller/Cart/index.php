<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Controller\Cart;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    private $logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session\Proxy $session,
        \Skybox\Checkout\Helpers\Util $util,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        $this->util = $util;
        $this->logger = $logger;
    }

    public function execute() {
        try {
            $cartId = $this->session->getQuote()->getId();
            $items = $this->session->getQuote()->getAllVisibleItems();
            $result = [];
            foreach ($items as $item) {
                $result[] = $this->util->getDataProduct($item, $cartId);
            }
            return $this->resultJson($result);
        }
        catch (\Exception $ex) {
            $this->logger->debug("[SBC] Index::execute Error => " . $ex->getMessage());
            return $this->resultJson(["success" => $ex->getMessage()]);
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
