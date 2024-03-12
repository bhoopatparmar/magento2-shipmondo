<?php

namespace Salecto\Shipmondo\Model\Layout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Salecto\Shipmondo\Model\Carrier\Shipmondo;

class AdminhtmlShipmondoProcessor implements LayoutProcessorInterface
{
    const SHIPPING_ADDITIONAL = 'children/shipmondo-parcelshop';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var Shipmondo
     */
    private $shipmondo;

    /**
     * @param ArrayManager $arrayManager
     * @param Shipmondo $shipmondo
     */
    public function __construct(
        ArrayManager $arrayManager,
        Shipmondo $shipmondo
    ) {
        $this->arrayManager = $arrayManager;
        $this->shipmondo = $shipmondo;
    }

    public function process($jsLayout)
    {
        
        $base = $this->arrayManager->get(static::SHIPPING_ADDITIONAL, $jsLayout);

        $jsLayout = $this->arrayManager->set(static::SHIPPING_ADDITIONAL, $jsLayout, array_merge($base, [
            'config' => [
                'mapped_shipmondo_codes' => $this->shipmondo->getMappedShipmondoCodes()
            ]
        ]));
        
        return $jsLayout;
    }
}
