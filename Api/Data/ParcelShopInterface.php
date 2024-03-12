<?php

namespace Salecto\Shipmondo\Api\Data;

interface ParcelShopInterface extends \Salecto\Shipping\Api\Data\ParcelShopInterface
{
    const ID = 'id';
    const NAME = 'name';
    const ADDRESS = 'address';
    const ADDRESS2 = 'address2';
    const ZIPCODE = 'zipcode';
    const CITY = 'city';
    const TELEPHONE = 'telephone';
    const LONGITUDE = 'longitude';
    const LATITUDE = 'latitude';
    const OPENING_HOURS = 'opening_hours';
    const COUNTRY_CODE = 'country';
    const CARRIER_CODE = 'carrier_code';


    /**
     * @return string|null
     */
    public function getCarrierCode();

    /**
     * @param string $string
     * @return ParcelShopInterface
     */
    public function setCarrierCode($string): ParcelShopInterface;
}
