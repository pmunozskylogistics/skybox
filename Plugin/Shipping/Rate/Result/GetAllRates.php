<?php
namespace Skybox\Checkout\Plugin\Shipping\Rate\Result;

class GetAllRates {
    public function afterGetAllRates($subject, $result) {
        foreach($result as $key => $rate) {
            if ($rate->getIsDisabled()) {
                unset($result[$key]);
            }
        }
        return $result;
    }
}

