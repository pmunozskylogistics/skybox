<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Controller\Product;

class Information extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session\Proxy $session,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Skybox\Checkout\Helpers\Util $util,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->formKey = $formKey;
        $this->stockItemRepository = $stockItemRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        $this->logger = $logger;
        $this->_productloader = $_productloader;
        $this->util = $util;
        $this->productRepository = $productRepository;
    }

    public function execute() {
        try {
            if ( isset( $_POST['products'] ) ) {              
                $products = json_decode(json_encode($_POST['products']), FALSE);
            }else{
                $rawdata = file_get_contents("php://input");
                $decoded = json_decode($rawdata);
                $products = $decoded->products;
            }

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $result = array();

            foreach ($products as $key => $product) {
                $productMerchantId = isset($product->{"productMerchantId"}) ? $product->productMerchantId : 0 ;
                $variantMerchantId = isset($product->{"variantMerchantId"}) ? $product->variantMerchantId : 0 ;
                
                $result_by_product = array(
                  "productMerchantId" => $productMerchantId,
                  "variantMerchantId" => $variantMerchantId,
                  "result" => true,
                );

                if ($productMerchantId > 0) {
                    $product_merchant = $objectManager->create('Magento\Catalog\Model\Product')->load($productMerchantId);
                    $product_validation = true;

                    if(!$product_merchant->getCreatedAt()){
                        $result_by_product["reason"] = "product merchant doesn't exist";
                        $product_validation = false;
                    }

                    if($productMerchantId != $variantMerchantId){                            
                        $product_variant = $objectManager->create('Magento\Catalog\Model\Product')->load($variantMerchantId);
                        if(!$product_variant->getCreatedAt()){
                            $result_by_product["reason"] = "product variant doesn't exist";
                            $product_validation = false;
                        }
                        $result_by_product["name"] = $product_variant->getName();
                        $result_by_product["image"] = $this->util->getImageUrl($product_variant);
                    }else{
                        $result_by_product["name"] = $product_merchant->getName();
                        $result_by_product["image"] = $this->util->getImageUrl($product_merchant);
                    }

                    $result_by_product["result"] = $product_validation;
                    
                    if ($product_validation) {

                        $result_by_product["stock"] = $this->getStockItem($variantMerchantId);
                        
                        if($productMerchantId != $variantMerchantId){
                            // Attributes
                            $superAttributes = $this->getSuperAttributeData($productMerchantId);
                            
                            foreach ($superAttributes as $key => $_superAttribute) {
                                if (null !== $product_variant->getCustomAttribute($_superAttribute)) {
                                    $superAttributesToEvaluate[$key] = $product_variant->getCustomAttribute($_superAttribute)->getValue();
                                }
                            }

                            $result_by_product["super_attribute"] = $superAttributesToEvaluate;

                            // Options
                            $custom_options = $objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product_merchant);

                            if(count($custom_options) > 0){
                                $options = array();
                                
                                foreach ($custom_options as $key => $_custom_option) {
                                    $options[] = array(
                                      "id_label" => $key,
                                      "label" => $_custom_option->getTitle(),
                                      "values" => array()
                                    );
                                }
                                
                                $result_by_product["options"] = $options;
                            }
                        }
                    }
                }else{
                  $result_by_product["reason"] = "the variant must be higher than zero.";
                }

                $result[] = $result_by_product;
            }

            return $this->resultJson([
              "success" => true,
              "products" => $result,
              "form_key" => $this->formKey->getFormKey()
            ]);
            
            /*$sku = $this->getRequest()->getParam('sku');
            $product = $this->productRepository->get($sku);
            return $this->resultJson([
                "success" => true,
                "product" => [
                  "name"  => $product->getName(),
                  "image"  => $this->util->getImageUrl($product),
                ]
            ]);*/
        }
        catch(\Exception $ex) {
            $this->logger->debug("[SBC] Index::execute Error => " . $ex->getMessage());
            return $this->resultJson([
              "success" => false,
              "message" => $ex->getMessage()
            ]);
        }
    }

    public function getStockItem($productId) {
        return $this->stockItemRepository->get($productId)->getQty();
    }

    public function getSuperAttributeData($productId) {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($productId);

        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return [];
        }
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($product->getStoreId(), $product);
        $attributes = $productTypeInstance->getConfigurableAttributes($product);

        $superAttributeList = [];
        foreach($attributes as $_attribute){
            $attributeCode = $_attribute->getProductAttribute()->getAttributeCode();;
            $superAttributeList[$_attribute->getAttributeId()] = $attributeCode;
        }

        return $superAttributeList;
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