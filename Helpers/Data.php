<?php
namespace Skybox\Checkout\Helpers;

class Data {
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;
    private $config;
    private $logger;

    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Skybox\Checkout\Helpers\Config $configHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->session = $session;
        $this->config = $configHelper;
        $this->logger = $logger;
    }

    public function getCommodities() {
        $commodities = $this->session->getCommodities();
        if (!isset($commodities)){
            $token = $this->getToken();
            $commodities = $this->loadCommodities($token);
            $this->session->setCommodities();
        }
        return $commodities;
    }

    private function getToken() {
        $url = $this->config->getApiUrl() . 'authenticate';
        $params = [
            "Merchant" => [
                "Id" => $this->config->getMerchantCode(),
                "Key" => $this->config->getMerchantKey()
            ]
        ];
        $headers = ["Content-Type: application/json"];

        $responseBody = "";

        $curl = curl_init();
        try {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));

            $responseBody = curl_exec($curl);
        }
        catch(\Exception $ex) {
            $this->logger->debug("[SBC] Data::getToken Error => " . $ex->getMessage());
            throw $ex;
        }
        finally {
            curl_close($curl);
        }

        $response = json_decode($responseBody);

        return $response->Data->Token;
    }

    private function loadCommodities($token) {
        $url = $this->config->getApiUrl() . 'commodities';
        $headers = [
            "Content-Type: application/json",
            "Authorization: $token",
            "X-Skybox-Merchant-Id: " . $this->config->getMerchantCode()
        ];

        $responseBody = "";

        $curl = curl_init();
        try {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $responseBody = curl_exec($curl);
        }
        catch(\Exception $ex) {
            $this->logger->debug("[SBC] Data::getToken Error => " . $ex->getMessage());
            throw $ex;
        }
        finally {
            curl_close($curl);
        }

        $response = json_decode($responseBody);

        return $response->Data->Commodities;
    }

    public function getCustomer(){

        $merchantId = $this->config->getMerchantId();

        if (!$merchantId) {
            return null;
        }

        $parts = explode("*", $merchantId ?? "");

        if(count($parts)>1){
            //EXTERNAL LINK
            $merchantId = $parts[0];
        }

        $url        = $this->config->getApiUrl() . 'store/' . $merchantId . '/ipdata';
        
        $headers = [
            "Content-Type: application/json"
        ];
        
        $responseBody = "";

        $curl = curl_init();
        try {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $responseBody = curl_exec($curl);
        }
        catch(\Exception $ex) {
            $this->logger->debug("[SBC] Data::getToken Error => " . $ex->getMessage());
            throw $ex;
        }
        finally {
            curl_close($curl);
        }

        return json_decode($responseBody);
        
    }

    public function getCartData($cartId = '') {
        $url        = $this->config->getApiUrl() . 'authenticate/cart';
        
        $headers = [
            "Content-Type: application/json"
        ];            
        
        $params = [
            "Merchant"  =>  [
                "Id" => $this->config->getMerchantCode(),
                "Key" => $this->config->getMerchantKey()
            ],
            "CartId" => $cartId,
            "Customer"  =>  [
                "Ip"    =>  [
                    "Local"     => $_SERVER['REMOTE_ADDR'],
                    "Remote"    => $_SERVER['REMOTE_ADDR'], //$customer->Customer->Ip->Remote,
                    "Proxy"     => '' //$customer->Customer->Ip->Proxy
                ],
                "Browser"   => [
                    "Agent"     => "",
                    "Languages" => ""
                ]
            ]
        ];            

        $responseBody = "";
        
        $curl = curl_init();
        try {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));

            $responseBody = curl_exec($curl);
        }
        catch(\Exception $ex) {
            $this->logger->debug("[SBC] Data::getToken Error => " . $ex->getMessage());
            throw $ex;
        }
        finally {
            curl_close($curl);
        }

        $response = json_decode($responseBody);

        $integrationType = $response->Data->IntegrationType;            
        $locationAllow = $response->Data->LocationAllow;
        $_SESSION["integrationType"] = $integrationType;
        $_SESSION["locationAllow"] = $locationAllow;
    }

    public function getLocationAllow() {
        if(!array_key_exists("locationAllow", $_SESSION)){
            $this->getCartData();
        }
        $locationAllow = $_SESSION["locationAllow"];
        return $locationAllow;
        
    }

    public function getIntegrationType()
    {
        $_SESSION["integrationType"] = 0;

        if(array_key_exists("integrationType", $_SESSION)){
            $integrationType = $_SESSION["integrationType"];
        }else{
            $integrationType = 0;
        }
        
        if($integrationType == 0 || is_null($integrationType)){
            
            $url        = $this->config->getApiUrl() . 'authenticate/cart';
            $customer     = $this->getCustomer();

            if (is_null($customer)) {
                return 0;
            }
            
            $headers = [
                "Content-Type: application/json"
            ];            
            
            $params = [
                "Merchant"  =>  [
                    "Id" => $this->config->getMerchantCode(),
                    "Key" => $this->config->getMerchantKey()
                ],
                "Customer"  =>  [
                    "Ip"    =>  [
                        "Local"     => $customer->Customer->Ip->Local,
                        "Remote"    => $customer->Customer->Ip->Remote,
                        "Proxy"     => $customer->Customer->Ip->Proxy
                    ],
                    "Browser"   => [
                        "Agent"     => "",
                        "Languages" => ""
                    ]
                ]
            ];            

            $responseBody = "";
            
            $curl = curl_init();
            try {
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));

                $responseBody = curl_exec($curl);                
            }
            catch(\Exception $ex) {
                $this->logger->debug("[SBC] Data::getToken Error => " . $ex->getMessage());
                throw $ex;
            }
            finally {
                curl_close($curl);
            }

            $response = json_decode($responseBody);
            $integrationType = $response->Data->IntegrationType;            
            $_SESSION["integrationType"] = $integrationType;            
        }
        return $integrationType;
    }
}

