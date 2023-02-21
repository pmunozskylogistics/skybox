<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 */

namespace Skybox\Checkout\Api;

/**
 * Search product by url-key
 *
 * @package Skybox\Checkout\Api
 */
interface ProductByInterface
{
    /**
     * POST product identified by its URL key
     *
     * @api
     * @param string $urlKeys
     * @return string Return example data
    */
    public function getProductByUrl($urlKeys);
}