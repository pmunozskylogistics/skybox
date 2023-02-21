<?php
/**
 * Copyright © 2017 SkyBOX Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Model\Config\Source;

class Commodities implements \Magento\Framework\Option\ArrayInterface
{
    private $options;

    /**
     * @var \Skybox\Checkout\Helpers\Data
     */
    private $dataHelper;
    private $messageManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Skybox\Checkout\Helpers\Data $dataHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->logger         = $logger;
        $this->dataHelper     = $dataHelper;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!isset($this->options)) {
            $this->options = $this->getData();
        }

        return $this->options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            123 => __('Default Commodity - 123 '),
            124 => __('Another Commodity - 124 '),
        ];
    }

    private function getData()
    {
        $data = [];
        $data[] = [
            'value' => '',
            'label' => '',
        ];

        try {
            $commodities = $this->dataHelper->getCommodities();
            foreach($commodities as $item) {
                $data[] = [
                    'value' => $item->Id,
                    'label' => $item->Description
                ];
            }
        }
        catch(\Exception $ex) {
            $this->messageManager->addErrorMessage("Verify your Skybox Checkout Configuration");
        }

        return $data;
    }
}
