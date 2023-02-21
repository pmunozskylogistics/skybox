<?php
namespace Skybox\Checkout\Plugin\Shipping\Rate\Result;

class Append {
	private $inFront = false;

        public function __construct(
		\Magento\Framework\App\Request\Http $request,
                \Psr\Log\LoggerInterface $logger
        ) {
                $this->logger = $logger;
		$logger->debug('Append constructor => ' . $request->getRequestString());
		$this->inFront = (substr($request->getRequestString(), 0, 13) == '/rest/default');
        }

        public function beforeAppend($subject, $result) {
                if (!$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
                        return [$result];
                }
		
		if ($result->getCarrier() == 'skbshipping' && $this->inFront) {
			$this->logger->debug('disabled Carrier => ' . $result->getCarrier());
			$result->setIsDisabled(true);
		}
                return [$result];
        }
}


