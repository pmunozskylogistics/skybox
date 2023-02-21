<?php

namespace Skybox\Checkout\Block\Product;
use Pulsestorm\TutorialInstanceObjects\Model\Example;

class AfterPrice extends \Magento\Framework\View\Element\Template {
    /**
     *@var \Magento\Framework\App\Request\Http
     */
    public $_request;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Skybox\Checkout\Helpers\Util
     */
    private $util;

    /**
     *@var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository; 

     /**
      * @var \Magento\Framework\Registry
      */
    private $_registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Product $product,
        \Skybox\Checkout\Helpers\Config $configHelper,
        \Skybox\Checkout\Helpers\Util $util,
        \Magento\Framework\App\Request\Http $request,        
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
        )
    {
        parent::__construct($context);
        $this->logger            = $context->getLogger();
        $this->product           =  $product;
        $this->configHelper      = $configHelper;
        $this->util              = $util;
        $this->_request          = $request;
        $this->productRepository = $productRepository;
        $this->_registry         = $registry;
    }
    
    public function isEnabled() {
        return $this->configHelper->isEnabled();
    }

    public function getConfig() {
		return $this->configHelper;
	}

    public function getProductId() {
        return $this->product->getId();
    }

    public function onCatalog()
    {
        if ($this->_request->getFullActionName() == 'catalog_product_view') {
            return false;
        }
        if ($this->_request->getFullActionName() == 'catalog_category_view') {
            //you are on the category page
            return true;
        }
        return true;
    }

    /**
     * Return the id of the main product on the current product page
     *
     * @return int
     */
    public function isMainProduct(){
        $currentProduct = $this->_registry->registry('current_product');
        if(is_null($currentProduct)){
            return false;
        }
        return ($this->product->getId() == $currentProduct->getId()) ? true : false ;        
    }

    public function hasChilds(){
        //@todo add conditionals to products not configurables but with childs, ex grouped products.       
        if ($this->product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return true;
        }else{
            return false;
        }
    }

    public function hasCustomOption(){
        
        if(count($this->product->getOptions())>0) {            
            return true;
        }else{
            return false;
        }
    }

    public function getChildsData()
    {
        $childrenData = [];

        if($this->hasChilds()){
            $children = $this->product->getTypeInstance()->getUsedProducts($this->product);
            foreach ($children as $child){
                $childrenData[$child->getId()] = $this->getProductJsonFormat($child);
            }
        }
        return $childrenData;
    }

    public function getProductData() {
        return $this->getProductJsonFormat($this->product);
    }

    public function getProductJsonFormat($product, $id = 0, $priceToAdd = 0){
        
        $variantId = ($id == 0) ? $product->getId(): $id;
        $p = $this->productRepository->get($product->getSku());
        $pData =  $p->getData();
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
        $jsonText = str_replace("'", "%27", json_encode($data));

        return $jsonText;
    }


    public function getAllAttributesOptions(){

        $productAttrOption = array();

        if($this->hasChilds()){
            $attributesByProduct    = $this->product->getTypeInstance()->getConfigurableOptions($this->product);        
            $productAttrOption['total_att'] = count($attributesByProduct);
            foreach ($attributesByProduct as $attributeId => $ProductList) {
                foreach ($ProductList as $product) {
                    $oProduct = $this->productRepository->get($product['sku']);
                    $productAttrOption[$oProduct->getId()][$attributeId] = $product['value_index'];
                }
            }
        }
        
        return $productAttrOption;
    }
}
