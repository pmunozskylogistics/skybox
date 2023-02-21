<?php
/**
 * Copyright © 2017 SkyBOX Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Controller\International;

class Success extends \Magento\Framework\App\Action\Action
{
    private $resultPageFactory;
    private $helper;
    private $interfaceObjectManager;
    private $urlHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * Success constructor.
     *     
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Url $urlHelper
     * @param \Magento\Catalog\Model\Session\Proxy $checkoutSession
     */
    public function __construct(        
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Url $urlHelper,
        \Magento\Catalog\Model\Session\Proxy $checkoutSession
    ) {        
        $this->urlHelper         = $urlHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession   = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Checkout\Model\Cart $cartObject */
        $cartObject = $this->objectManager('\Magento\Checkout\Model\Cart');
        $items      = $cartObject->getQuote()->getAllItems();

        if (!empty($items)) {
            $this->checkoutSession->setUpdateLocalStorage(1);
            $resultPage     = $this->resultPageFactory->create();
            $block          = $this->getView()->getLayout()->getBlock('skb.international.success');
            $cartObject->getQuote()->removeAllItems();
            $cartObject->saveQuote();
            $this->checkoutSession->unsStoreObject();
            return $resultPage;
        }else{
            $resultPage     = $this->resultPageFactory->create();
            $block          = $this->getView()->getLayout()->getBlock('skb.international.success');
            return $resultPage;
        }
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    private function objectManager($name)
    {
        if (empty($interfaceObjectManager)) {
            $this->interfaceObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }

        return $this->interfaceObjectManager->get($name);
    }

    /**
     * @return mixed
     */
    private function getView()
    {
        if (empty($this->view)) {
            $this->view = $this->objectManager('\Magento\Backend\Model\View\Result\Page');
        }

        return $this->view;
    }
}
