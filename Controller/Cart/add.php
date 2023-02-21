<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Controller\Cart;

class Add extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session\Proxy $session,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->formKey = $formKey;
        $this->stockItemRepository = $stockItemRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->session = $session;
        $this->logger = $logger;
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
            
            $cart     = $this->session->getQuote();
            $items    = $this->session->getQuote()->getAllVisibleItems();

            $response = array();
            $lastProductKeyVisited = -1;

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    
            if ($products) {
                
                foreach ($products as $key => $product) {

                    $lastProductKeyVisited = $key;
                    $productMerchantId = isset($product->{"productMerchantId"}) ? $product->productMerchantId : 0 ;
                    $variantMerchantId = isset($product->{"variantMerchantId"}) ? $product->variantMerchantId : 0 ;
                    $quantity = isset($product->{"quantity"}) ? $product->quantity : 1 ;

                    $productMerchantId = $productMerchantId > 0 ? $productMerchantId : $variantMerchantId;

                    $product_response = array(
                      "productMerchantId" => $productMerchantId,
                      "variantMerchantId" => $variantMerchantId,
                      "quantity" => $quantity,
                      "added" => false,
                    );
                    
                    if ($productMerchantId > 0) {
                        
                        $product_merchant = $objectManager->create('Magento\Catalog\Model\Product')->load($productMerchantId);
                        $product_validation = true;

                        if(!$product_merchant->getCreatedAt()){
                            $product_response["reason"] = "product merchant doesn't exist";
                            $product_validation = false;
                        }

                        $stock = 0;
                        
                        if($productMerchantId != $variantMerchantId){                            
                            $product_variant = $objectManager->create('Magento\Catalog\Model\Product')->load($variantMerchantId);
                            if(!$product_variant->getCreatedAt()){
                                $product_response["reason"] = "product variant doesn't exist";
                                $product_validation = false;
                            }
                        }

                        $params = array(
                          "form_key" => $this->formKey->getFormKey(),
                          "product" => $productMerchantId,
                          "qty" => $quantity
                        );
                        
                        if ($product_validation) {

                            $stock = $this->getStockItem($variantMerchantId);

                            if ($quantity <= $stock){
                                if($productMerchantId != $variantMerchantId){
                                    // Attributes
                                    $superAttributes = $this->getSuperAttributeData($productMerchantId);
                                    
                                    foreach ($superAttributes as $key => $_superAttribute) {
                                        if (null !== $product_variant->getCustomAttribute($_superAttribute)) {
                                            $superAttributesToEvaluate[$key] = $product_variant->getCustomAttribute($_superAttribute)->getValue();
                                        }
                                    }

                                    $params["super_attribute"] = $superAttributesToEvaluate;

                                    // Options
                                    $validation_options = true;
                                    if (isset($product->{"options"})) {

                                        $custom_options = $objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product_merchant);

                                        if(count($custom_options) > 0){
                                            $options_add = array();
                                            $input_options = $product->{"options"};

                                            foreach ($input_options as $_input_option) {
                                                foreach ($custom_options as $key => $_custom_option) {
                                                    if($_custom_option->getTitle() === $_input_option->{"label"}){
                                                        $options_add[$key] = $_input_option->{"value"};
                                                    }
                                                }
                                            }
                                            $params["options"] = $options_add;
                                        }else{
                                            $validation_options = false;
                                            $product_response["reason"] = "The product hasn´t custom options";
                                        }
                                    }
                                }

                                if ($validation_options) {
                                    try {
                                        $this->cart->addProduct($product_merchant, $params);
                                        $product_response["added"] = true;
                                    } catch(\Exception $ex) {                              
                                        $product_response["reason"] =  $ex->getMessage();
                                    } 
                                }
                                  
                            }else{
                                $product_response["reason"] = "Insufficient stock: " . $stock;
                            }
                        }
                    }else{
                      $product_response["reason"] = " productMerchantId < 0";
                    }
                    $response[] = $product_response;
                }

                $this->cart->save();
            } else {
                throw new Exception("Product key not found", 1);
            }

            // foreach($items as $item) {
            //     $this->cart->removeItem($item->getItemId())->save();
            // }
            return $this->resultJson(["success" => true, "response" => $response]);
        }
        catch(\Exception $ex) {
            $this->logger->debug("[SBC] Index::execute Error => " . $ex->getMessage());
            return $this->resultJson(["success" => false, "error" => $ex->getMessage() . " .Posible product error: " . $lastProductKeyVisited]);
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

        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        $routeName = $this->getRequest()->getRouteName();
        $moduleName = $this->getRequest()->getModuleName(); 

        // $data["controllerName"] = $controllerName;
        // $data["actionName"] = $actionName;
        // $data["routeName"] = $routeName;
        // $data["moduleName"] = $moduleName;

        $result = $this->resultJsonFactory->create();
        $result->setHeader('Content-type', 'aplication/json; charset=UTF-8');
        $result->setHttpResponseCode(\Magento\Framework\Webapi\Response::HTTP_OK);
        $result->setData($data);

        return $result;
    }    
}
