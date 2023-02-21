<?php
namespace Skybox\Checkout\Controller;
 
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;
 
    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
 
    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
        $this->logger = $logger;
    }
 
    /**
     * Validate and Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        /*
         * We will search “examplerouter” and “exampletocms” words and make forward depend on word
         * -examplerouter will forward to base router to match inchootest front name, test controller path and test controller class
         * -exampletocms will set front name to cms, controller path to page and action to view
         */
        $identifier = trim($request->getPathInfo(), '/');
        $matchCheck = [];
        $matchSucc = [];
        preg_match('/skbcheckout\/checkout/', $identifier, $matchCheck);
        preg_match('/skbcheckout\/success/', $identifier, $matchSucc);
        if (count($matchCheck) > 0) {
            /*
             * We must set module, controller path and action name + we will set page id 5 witch is about us page on
             * default magento 2 installation with sample data.
             */
            $request->setModuleName('skbcheckout')->setControllerName('international')->setActionName('index');
        } else if (count($matchSucc) > 0) {
            $request->setModuleName('skbcheckout')->setControllerName('international')->setActionName('success');
        } else {
            //There is no match
            return;
        }
 
        /*
         * We have match and now we will forward action
         */
        return $this->actionFactory->create(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        );
    }
}
