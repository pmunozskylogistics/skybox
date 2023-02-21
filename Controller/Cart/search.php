<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Controller\Cart;

use Magento\Catalog\Model\Product\Visibility;


class Search extends \Magento\Framework\App\Action\Action
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
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
	\Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        $this->util = $util;
        $this->logger = $logger;
        $this->productCollection = $productCollection;
	$this->productRepository = $productRepository;
    }

    public function execute() {

        try {

            try {
                
                $stringProducts = $this->getRequest()->getParam('products');
                $arrayProducts = json_decode("$stringProducts");                                
                $arrayQuery = [];

                foreach($arrayProducts as $p) {
                    $arrayQuery[] = trim($p->url);
                }
                
		//var_dump($arrayQuery); 
                $products = $this->productCollection->create();
                $products->addFieldToSelect(['id','sku','name','url']);
                $products->addFieldToFilter('url', ['in' => $arrayQuery]);
                //$products->addFieldToFilter('name', ['='=>'Radiant Tee']);                
                $products->addFieldToFilter('visibility',['in'=>[Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_IN_SEARCH]]);
                //$products->setPageSize(count($arrayProducts));
                $products->setCurPage(1);

                foreach ($products->getItems() as $product) { 
			$p = $this->productRepository->getById($product->getId());
			foreach($arrayProducts as $item) {	
//			    echo trim($item->url) .'=='. trim($p->getProductUrl())."\n";			
	                    if(trim($item->url) === trim($p->getProductUrl())){
				$data = $this->getProductJsonFormat($p);
				$responseProduct[] = array('pos'=>$item->pos,'data'=>$this->getProductJsonFormat($p));
				break;
			    }
                	}			
                }

		return $this->resultJson($responseProduct);
    
            } catch (\Exception $ex) {
                $this->logger->debug("[SBC] Index::execute Error => " . $ex->getMessage());
                return $this->resultJson(["success" => false, "messageException" => $ex->getMessage()]);
            }

            /*$cartId = $this->session->getQuote()->getId();
            $items = $this->session->getQuote()->getAllVisibleItems();
            $result = [];
            foreach ($items as $item) {
                $result[] = $this->util->getDataProduct($item, $cartId);
            }
            return $this->resultJson($result);*/
        } catch (\Exception $ex) {
            $this->logger->debug("[SBC] Index::execute Error => " . $ex->getMessage());
            return $this->resultJson(["success" => false]);
        }
    }

    public function getProductJsonFormat($product, $id = 0, $priceToAdd = 0){
        
        $variantId = ($id == 0) ? $product->getId(): $id;
        //$p = $this->productRepository->get($product->getSku());i
        $pData =  $product->getData();
        $weight = array_key_exists('weight', $pData)? $pData['weight'] : 0;

        $data = [
            'variantID' => $variantId,
            'name' => $product->getName(),
            'price' =>  $this->util->getFinalPrice($product) + $priceToAdd,
            'image' => $this->util->getImageUrl($product),
            'sku' =>  $product->getSku(),
            'weight' => $weight,
            'category' => $this->util->getCommodity($product)
        ];
        //$jsonText = str_replace("'", "%27", json_encode($data));

        return $data;
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

