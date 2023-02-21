<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Controller\ChangeCountry;

use Magento\Catalog\Model\Product\Visibility;


class Index extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Skybox\Checkout\Helpers\Data $util,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->util = $util;
        $this->logger = $logger;
    }

    public function execute() {
        $url = $this->getRequest()->getParam('url');
        $strs = explode('***', $url ?? "");

        $this->util->getCartData($strs[0]);

        $toUrl = $strs[1];
        if (!$toUrl)
            $toUrl = '';

	return $this->_redirect($toUrl);
    }
}

