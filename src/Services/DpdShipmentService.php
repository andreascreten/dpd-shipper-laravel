<?php

namespace Flooris\DpdShipper\Services;

use Flooris\DpdShipper\Objects\DpdPaperFormat;
use SoapFault;
use Flooris\DpdShipper\Objects\DpdSender;
use Flooris\DpdShipper\Objects\DpdPredict;
use Flooris\DpdShipper\Objects\DpdParcels;
use Flooris\DpdShipper\Objects\DpdRecipient;
use Flooris\DpdShipper\Objects\DpdShipmentLabel;
use Flooris\DpdShipper\Objects\DpdShipmentProduct;
use Flooris\DpdShipper\Exceptions\DpdAuthenticationException;
use Flooris\DpdShipper\Exceptions\DpdShipmentResponseException;

class DpdShipmentService extends AbstractDpdService
{
    const SERVICE_NAME = 'ShipmentService';
    const SERVICE_VERSION = 'V34';
    const SERVICE_METHOD_NAME = 'storeOrders';

    private string $printerLanguage = 'PDF';
    private string $paperFormat = 'A4';

    public function setPaperFormat(DpdPaperFormat $paperFormat): self
    {
        $this->paperFormat = $paperFormat->value;

        return $this;
    }

    /**
     * @throws SoapFault
     * @throws DpdShipmentResponseException
     * @throws DpdAuthenticationException
     */
    public function createShipment(
        DpdShipmentProduct $shipmentProduct,
        int                $parcelCount,
        DpdParcels         $parcels,
        DpdSender          $sender,
        DpdRecipient       $recipient,
        ?DpdPredict        $dpdPredict
    ): DpdShipmentLabel
    {
        $parcelsArray = [];
        for ($parcel_pointer = 0; $parcel_pointer < $parcelCount; $parcel_pointer++) {
            $parcelData = [
                'customerReferenceNumber1' => $parcels->getCustomerReferenceNumber1(),
                'customerReferenceNumber2' => $parcels->getCustomerReferenceNumber2(),
                'weight'                   => $parcels->getWeight(),
            ];

            if ($shipmentProduct->isDpdFresh()) {
                $parcelData['goodsExpirationDate'] = $parcels->getExpirationDate()->format('Ymd');

                if($shipmentProduct->getProductType()->hasMinMaxTemperatures()) {
                    $parcelData['goodsMinimumStorageTemperature'] = $shipmentProduct->getProductType()->getMinimumStorageTemperature();
                    $parcelData['goodsMaximumStorageTemperature'] = $shipmentProduct->getProductType()->getMaximumStorageTemperature();
                }
            }

            // ToDo: Implement international shipping

            $parcelsArray[] = $parcelData;
        }

        $shipmentData = [
            'printOptions' => [
                'printerLanguage' => $this->printerLanguage,
                'paperFormat'     => $this->paperFormat,
            ],
            'order'        => [
                'generalShipmentData'   => [
                    'sendingDepot' => $this->connector->depotNumber,
                    'product'      => $shipmentProduct->getServiceProductCode(),
                    'sender'       => $sender->toArray(),
                    'recipient'    => $recipient->toArray(),
                ],
                'parcels'               => $parcelsArray,
                'productAndServiceData' => [
                    'orderType' => 'consignment',
                ],
            ],
        ];

        $shipmentData['order']['generalShipmentData']['recipient']['type'] = $recipient->type;

        if ($dpdPredict) {
            $shipmentData['order']['productAndServiceData']['predict'] = [
                'channel'  => $dpdPredict->getChannel(),
                'value'    => $dpdPredict->getValue(),
                'language' => $dpdPredict->getLanguage(),
            ];
        }

        if ($shipmentProduct->useSaturdayShipping()) {
            $shipmentData['order']['productAndServiceData']['saturdayDelivery'] = true;
        }

        if ($shipmentProduct->isDpdFresh()) {
            $shipmentData['order']['productAndServiceData']['tour'] = $shipmentProduct->getProductType()->getTour();
        }

        try {

            $result = $this->doSoapRequest(
                serviceName: self::SERVICE_NAME,
                serviceVersion: self::SERVICE_VERSION,
                soapMethod: self::SERVICE_METHOD_NAME,
                soapHeader: $this->connector->getSoapAuthenticationHeader(),
                data: $shipmentData
            );

        } catch (SoapFault $e) {
            if (isset($e->detail) && isset($e->detail->faults)) {
                throw new DpdShipmentResponseException($e->detail->faults->message);
            }

            $this->connector->forgetApiTokenFromCache();
            $this->connector->loginService()->getApiToken();
            throw $e;
        } catch (\Exception $e) {
            $lastResponse = $this->getSoapLastResponse();
            $orderId      = $parcels->getCustomerReferenceNumber1();
            $message      = "DpdDisConnector (storeOrders) - Order ID: {$orderId} - response: {$lastResponse} - unknown Exception message: ";
            $message      .= $e->getMessage();

            throw new DpdShipmentResponseException($message);
        }

        if (isset($result->orderResult->shipmentResponses->faults)) {
            throw new DpdShipmentResponseException($result->orderResult->shipmentResponses->faults->message);
        }

        if (! isset($result->orderResult->shipmentResponses->mpsId)) {
            $message = "DPD API -> orderResult doesn't contain the MPS ID ! ";

            throw new DpdShipmentResponseException($message, $shipmentData, $result->orderResult);
        }

        $mps_id             = $result->orderResult->shipmentResponses->mpsId;
        $pdf_data           = $result->orderResult->parcellabelsPDF;
        $parcel_information = $result->orderResult->shipmentResponses->parcelInformation;

        $barcodes = [];
        if (is_array($parcel_information)) {
            foreach ($parcel_information as $barcode) {
                $barcodes[] = $barcode->parcelLabelNumber;
            }
        } else {
            $barcodes[] = $parcel_information->parcelLabelNumber;
        }

        return new DpdShipmentLabel(
            $mps_id,
            $barcodes,
            $pdf_data,
            $this->printerLanguage
        );
    }
}
