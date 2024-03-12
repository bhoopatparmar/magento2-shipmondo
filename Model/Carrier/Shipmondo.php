<?php

namespace Salecto\Shipmondo\Model\Carrier;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\Repository;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Salecto\Shipmondo\Api\Carrier\ShipmondoInterface;
use Salecto\Shipmondo\Api\Data\ParcelShopInterface;
use Salecto\Shipmondo\Model\Api;
use Salecto\Shipping\Api\Carrier\MethodTypeHandlerInterface;
use Salecto\Shipping\Model\Carrier\AbstractCarrier;
use Salecto\Shipping\Model\RateManagement;

class Shipmondo extends AbstractCarrier implements ShipmondoInterface
{
    public $_code = self::TYPE_NAME;

    /**
     * @var Api
     */
    private $api;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param RateManagement $rateManagement
     * @param MethodFactory $methodFactory
     * @param ResultFactory $resultFactory
     * @param Api $api
     * @param Repository $assetRepository
     * @param StoreManagerInterface $storeManager
     * @param MethodTypeHandlerInterface|null $defaultMethodTypeHandler
     * @param null $shipmondoCarrierCode
     * @param null $componentName
     * @param null $backendLabel
     * @param array $methodTypeHandlers
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        RateManagement $rateManagement,
        MethodFactory $methodFactory,
        ResultFactory $resultFactory,
        Api $api,
        Repository $assetRepository,
        StoreManagerInterface $storeManager,
        MethodTypeHandlerInterface $defaultMethodTypeHandler = null,
        array $methodTypeHandlers = [],
        array $data = []
    ) {
        $this->api = $api;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $rateManagement,
            $methodFactory,
            $resultFactory,
            $assetRepository,
            $storeManager,
            $defaultMethodTypeHandler,
            $methodTypeHandlers,
            $data
        );
    }

    /**
     * Type name that links to the Rate model
     *
     * @return string
     */
    public function getTypeName(): string
    {
        return static::TYPE_NAME;
    }

    /**
     * @param $carrierCode
     * @param string $country
     * @param string|null $postcode
     * @param int $amount
     * @return ParcelShopInterface[]
     */
    public function getParcelShops($carrierCode, $country, $postcode = null, $amount = 30)
    {
        if (empty($postcode)) {
            return [];
        }
        try {
            $parcelShops = $this->api->getParcelShops($carrierCode, $country, $postcode, $amount);
        } catch (Exception $e) {
            return [];
        }

        if (empty($parcelShops) || !$parcelShops) {
            return [];
        }

        return $parcelShops;
    }

    /**
     * @param ShippingMethodInterface $shippingMethod
     * @param Rate $rate
     * @param string|null $typeHandler
     * @return mixed
     */
    public function getImageUrl(ShippingMethodInterface $shippingMethod, Rate $rate, $typeHandler)
    {
        try {
            $mapped = $this->getMappedShipmondoCodes();
            $shipmondoCode = $mapped[$shippingMethod->getExtensionAttributes()->getWexoShippingMethodType()];

            return $this->assetRepository->createAsset('Salecto_Shipmondo::images/' . $shipmondoCode . '.png', [
                'area' => Area::AREA_FRONTEND
            ])->getUrl();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getMappedShipmondoCodes()
    {
        $mapped = [];

        foreach ($this->getMethodTypesHandlers() as $methodTypesHandler) {
            $mapped[$methodTypesHandler['key']] = $methodTypesHandler['shipmondo_code'];
        }

        return $mapped;
    }

    /**
     * @return array
     */
    public function getMethodTypesHandlers(): array
    {
        $handlers = [];

        foreach ($this->methodTypesHandlers as $key => $method) {
            $handlers[$key] = [
                'label' => $method['label'],
                'type' => $method['type'] ?? $this->defaultMethodTypeHandler,
                'shipmondo_code' => $method['shipmondo_code'],
                'key' => $key
            ];
        }

        return $handlers;
    }
}
