<?php

namespace Salecto\Shipmondo\Model;

use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Salecto\Shipmondo\Api\Data\ParcelShopInterface;
use Salecto\Shipmondo\Model\Carrier\Shipmondo;

class Api
{
    /**
     * @var SimpleDataObjectConverter
     */
    private $simpleDataObjectConverter;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var Shipmondo
     */
    private $carrier;

    /**
     * @param SimpleDataObjectConverter $simpleDataObjectConverter
     * @param ObjectFactory $objectFactory
     * @param Shipmondo $carrier
     */
    public function __construct(
        SimpleDataObjectConverter $simpleDataObjectConverter,
        ObjectFactory $objectFactory,
        Shipmondo $carrier
    ) {
        $this->simpleDataObjectConverter = $simpleDataObjectConverter;
        $this->objectFactory = $objectFactory;
        $this->carrier = $carrier;
    }

    /**
     * @param $carrier_code
     * @param string $countryCode
     * @param string $zipCode
     * @param int $amount
     * @return array|false
     */
    public function getParcelShops($carrier_code, $countryCode, $zipCode, $amount)
    {
        return $this->mapParcelShops(
            $this->getClient()->getPickupPoints([
                'carrier_code' => $carrier_code,
                'country_code' => $countryCode,
                'zipcode' => $zipCode,
                'quantity' => $amount
            ])
        );
    }

    /**
     * @return \Shipmondo
     */
    public function getClient()
    {
        return new \Shipmondo(
            $this->carrier->getConfigData('public_key'),
            $this->carrier->getConfigData('private_key')
        );
    }

    /**
     * @param $parcelShops
     * @return array
     */
    protected function mapParcelShops($parcelShops)
    {
        return array_map(function ($parcelShop) {
            return $this->objectFactory->create(ParcelShopInterface::class, [
                'data' => $parcelShop
            ]);
        }, $parcelShops['output']);
    }
}
