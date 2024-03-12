<?php

namespace Salecto\Shipmondo\Plugins\Sales;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Data\ObjectFactory;
use Magento\Framework\Serialize\JsonValidator;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Salecto\Shipmondo\Api\Data\ParcelShopInterface;

class ShippingAdditionalDataPlugin
{
    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @var JsonValidator
     */
    private $jsonValidator;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var null
     */
    private $parcelShopClass;

    /**
     * @param SerializerInterface $json
     * @param JsonValidator $jsonValidator
     * @param DataObjectHelper $dataObjectHelper
     * @param ObjectFactory $objectFactory
     * @param null $parcelShopClass
     */
    public function __construct(
        SerializerInterface $json,
        JsonValidator       $jsonValidator,
        DataObjectHelper    $dataObjectHelper,
        ObjectFactory       $objectFactory,
        $parcelShopClass = null
    ) {
        $this->json = $json;
        $this->jsonValidator = $jsonValidator;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->objectFactory = $objectFactory;
        $this->parcelShopClass = $parcelShopClass;
    }

    /**
     * @param $order
     * @return array|null
     */
    protected function getShippingAdditionalData($order)
    {
        $shippingAdditionData = $shippingData = null;

        $wexoShippingData = $order->getData('wexo_shipping_data');
        if (is_string($wexoShippingData) && $this->jsonValidator->isValid($wexoShippingData)) {
            $shippingData = $this->json->unserialize($wexoShippingData);
        }

        if (isset($shippingData['parcelShop']) && !empty($shippingData['parcelShop'])) {
            /** @var ParcelShopInterface $parcelShop */
            $parcelShop = $this->objectFactory->create($this->parcelShopClass, []);
            $this->dataObjectHelper->populateWithArray($parcelShop, $shippingData['parcelShop'], $this->parcelShopClass);

            $shippingAdditionData = [
                "carrier_code" => $parcelShop->getCarrierCode(),
                "service_point_id" => $parcelShop->getNumber(),
                "service_point_country" => $parcelShop->getCountryCode()
            ];
        }

        return $shippingAdditionData;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param Order $order
     * @return Order
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        Order                    $order
    ) {
        $shippingAdditionalData = $this->getShippingAdditionalData($order);
        if (!empty($shippingAdditionalData)) {
            $order->getExtensionAttributes()->setData('shipping_additional_data', $this->json->serialize($shippingAdditionalData));
        }

        return $order;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param Collection $collection
     * @return Collection
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        Collection               $collection
    ) {
        $orders = [];

        /** @var OrderInterface $order */
        foreach ($collection->getItems() as $order) {
            $shippingAdditionalData = $this->getShippingAdditionalData($order);
            if (!empty($shippingAdditionalData)) {
                $order->getExtensionAttributes()->setShippingAdditionalData($this->json->serialize($shippingAdditionalData));
            }
            $orders[] = $order;
        }

        $collection->removeAllItems();
        $collection->setItems($orders);

        return $collection;
    }
}
