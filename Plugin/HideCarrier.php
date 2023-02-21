<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Skybox\Checkout\Plugin;

use Magento\Backend\Model\Auth\Session;

class HideCarrier
{
    private $backendSession;

    /**
     * HideCarrier constructor.
     *
     * @param Session $session
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $session,
        \Skybox\Checkout\Helpers\Data $dataHelpers
    )
    {
        $this->backendSession = $session;
        $this->dataHelper = $dataHelpers;
    }

    /**
     * @param $subject
     * @param callable $proceed
     *
     * @return bool
     */
    public function aroundCanCollectRates($subject, callable $proceed)
    {
        $subjectVar = $subject;
        if ($this->dataHelper->getLocationAllow() == 1) {
            return $proceed();
        }

        return false;
    }
}
