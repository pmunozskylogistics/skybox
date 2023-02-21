<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Controller\International;

class Index extends \Magento\Framework\App\Action\Action
{
    private $debug = false;
    private $resultPageFactory;
    private $interfaceObjectManager;
    private $urlHelper;
    private $view;
    public $assetRepository;
    private $checkoutSession;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context     
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\Session\Proxy $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,        
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        //\Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\Session\Proxy $checkoutSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->assetRepository   = $assetRepository;
        $this->checkoutSession   = $checkoutSession;
        //$this->logger            = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // DEBUGGER
        //$this->debug ? $this->logger->debug('[SBC] Controller\International\Index::execute') : false;

        $cart       = $this->objectManager('\Magento\Checkout\Model\Cart');
        $quote      = $cart->getQuote();
        $items      = $quote->getAllItems();

        $resultPage     = $this->resultPageFactory->create();
        $block          = $this->getView()->getLayout()->getBlock('skb.international.index');
        
        return $resultPage;
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
     * @param string $param
     *
     * @return string
     */
    private function filterParam($param)
    {
        return strip_tags($param);
    }

    /**
     * @param $routePath
     * @param array $routeParams
     *
     * @return mixed
     */
    private function getFrontendUrl($routePath, $routeParams = [])
    {
        if (empty($this->urlHelper)) {
            $this->urlHelper = $this->objectManager('\Magento\Framework\Url');
        }

        return $this->urlHelper->getUrl($routePath, $routeParams);
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
