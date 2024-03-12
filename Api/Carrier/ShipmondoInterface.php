<?php

namespace Salecto\Shipmondo\Api\Carrier;

use Salecto\Shipping\Api\Carrier\CarrierInterface;

interface ShipmondoInterface extends CarrierInterface
{
    const TYPE_NAME = 'shipmondo';

    /**
     * @param string $carrier_code
     * @param string $country
     * @param string|null $postcode
     * @param int $amount
     * @return \Salecto\Shipmondo\Api\Data\ParcelShopInterface[]
     */
    public function getParcelShops($carrier_code, $country, $postcode = null, $amount = 30);
}
