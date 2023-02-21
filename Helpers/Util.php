<?php

namespace Skybox\Checkout\Helpers;

class Util {
    private $imageHelper;
    private $store;
    private $logger;

    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Model\Store $store,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Psr\Log\LoggerInterface $logger,
        \Skybox\Checkout\Helpers\Config $configHelper
    ) {
        $this->imageHelper = $imageHelper;
        $this->store = $store;
        $this->productRepository = $productRepository;
        $this->categoryFactory = $categoryFactory;
        $this->logger = $logger;
        $this->configHelper = $configHelper;
    }

    public function getDataProduct(\Magento\Quote\Model\Quote\Item $quote, $cartId = 0) {
        $options = $quote->getProduct()->getTypeInstance(true)->getOrderOptions($quote->getProduct());
        $variantName = "";
        if (isset($options['attributes_info'])) {
            foreach($options['attributes_info'] as $opt) {
                $variantName .= "$opt[label]=$opt[value] ";
            }
        }

        $variantId = $quote->getProduct()->getIdBySku($quote->getSku());

        $sku = $quote->getSku();
        if($variantId === false){
            $variantId = $quote->getProductId();
            $product = $this->productRepository->getById($variantId);
            $sku = $product->getSku();
        }

        //$product = $this->productRepository->getById($variantId);

        return [
            "cart_id" => $cartId,
            "variant_id" => $variantId,
            "quote_id" => $quote->getItemId(),
            "product_title" => $quote->getName(),
            "weight" => ($quote->getWeight() === null || $quote->getWeight() === '')? 0 : $quote->getWeight(),
            "weightUnit" => $this->configHelper->getWeigthUnit(),
            "sku" => $sku,
            "variant_title" => $variantName,
            "category" => $this->getCommodity($quote->getProduct()),
            "price" => $quote->getPrice(),
            "image" => $this->getImageUrl($quote->getProduct()),
            "quantity" => $quote->getQty(),
            "options" => $this->getOptions($quote)
        ];
    }

    public function getFinalName(\Magento\Catalog\Model\Product $product) {
        switch ($product->getTypeId()) {
            case 'downloadable':
                $name = $product->getName();
                $link = $product->getLinkId();
                if (!empty($link)) {
                    $name = $product->getTitle();
                }
                break;
            default:
                $name = $product->getName();
                break;
        }

        return $name;
    }

    public function getImageUrl(\Magento\Catalog\Model\Product $product) {
        return $this->imageHelper->init($product, 'product_page_image_small')->getUrl();
    }

    public function getFinalPrice(\Magento\Catalog\Model\Product $product, $qty = 1) {
        return $price = $product->getFinalPrice($qty);
    }

    public function getCommodity(\Magento\Catalog\Model\Product $product) {
        $commodity = $product->getSkyboxCategoryId();
        if (empty($commodity)) {
            $prod = $this->productRepository->getById($product->getId());
            $commodity = $prod->getData('skybox_category_id');
        }

        if (!$commodity) {
            $commodity = $this->getCommodityFromCategory($product);
        }

        return empty($commodity) ? 0 : $commodity;
    }

    public  function getCommodityFromCategory(\Magento\Catalog\Model\Product $product) {
        $categories = $product->getCategoryIds();
        $commodity  = 0;

        if (!empty($categories)) {
            foreach($categories as $cat) {
                $_category = $this->categoryFactory->create()->load($cat);
                $commodity = $_category->getSkyboxCategoryId();    
                if ($commodity)
                    break;
            }
        }

        if (!$commodity) {
            $rootCategoryId = $this->store->getRootCategoryId();
            $_category      = $this->categoryFactory->create()->load($rootCategoryId);
            $commodity      = $_category->getSkyboxCategoryId();
        }

        return $commodity;        
    }

    public function getOptions(\Magento\Quote\Model\Quote\Item $quote) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $result = [];
        $itemOptions = $quote->getOptions();

        try{
            //$itemOptions = json_decode($itemOptions[0]['value'], true);
            $itemOptions = unserialize($itemOptions[0]['value']);
        }catch(Exception $e){
            $this->logger->debug("[SBC] Util::getOptions ERROR => " . $e->getMessage());
            $itemOptions = array();
        }
        
        if (is_array($itemOptions) && array_key_exists('options', $itemOptions)) {
            foreach($itemOptions['options'] as $key => $value) {
                $parentOptionId = $key;
                $optionId = $value;
                $option = $objectManager->get('\Magento\Catalog\Model\Product\Option')->load($parentOptionId);
                $productId = $option->getProductId();
                $product = $objectManager->get('\Magento\Catalog\Model\Product')->load($productId);
                $options = $objectManager->get('\Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
                foreach($options as $option){
                    $values = $option->getValues();
                    $setOption = false;
                    if ($values) {
                        foreach ($values as $val) {
                            if ($val->getOptionTypeId() == $optionId) {
                                $result[] = [
                                    "id" => $parentOptionId,
                                    "label" => $option->getTitle(),
                                    "idValue" => $optionId,
                                    "value" => $val->getTitle()
                                ];
                                $setOption = true;
                            }
                        }
                        if ($setOption) {
                            break;
                        }
                    }
                    else {
                        $result[] = [
                            "id" => $parentOptionId,
                            "label" => $option->getTitle(),
                            "idValue" => $value,
                            "value" => $value
                        ];
                    }
                }
            }
        }

        return $result;
    }
}
