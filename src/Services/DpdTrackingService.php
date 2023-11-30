<?php

namespace Flooris\DpdShipper\Services;

use SoapFault;
use Flooris\DpdShipper\Exceptions\DpdAuthenticationException;
use Flooris\DpdShipper\Exceptions\DpdTrackingResponseException;
use Flooris\DpdShipper\Objects\DpdParcelStatusInfo;

class DpdTrackingService extends AbstractDpdService
{
    const SERVICE_NAME = 'ParcelLifeCycleService';
    const SERVICE_VERSION = 'V20';
    const SERVICE_METHOD_NAME = 'getTrackingData';

    /**
     * @throws SoapFault
     * @throws DpdTrackingResponseException
     * @throws DpdAuthenticationException
     */
    public function getTrackingData(
        string $parcelLabelNumber,
    ): DpdParcelStatusInfo
    {
        try {
            $result = $this->doSoapRequest(
                serviceName: self::SERVICE_NAME,
                serviceVersion: self::SERVICE_VERSION,
                soapMethod: self::SERVICE_METHOD_NAME,
                soapHeader: $this->connector->getSoapAuthenticationHeader(),
                data: [
                    'parcelLabelNumber' => $parcelLabelNumber,
                ]
            );
        } catch (SoapFault $e) {
            if (isset($e->detail) && isset($e->detail->faults)) {
                throw new DpdTrackingResponseException($e->detail->faults->message);
            }

            $this->connector->forgetApiTokenFromCache();
            $this->connector->loginService()->getApiToken();
            throw $e;
        } catch (\Exception $e) {
            $lastResponse = $this->getSoapLastResponse();
            $orderId      = $parcels->getCustomerReferenceNumber1();
            $message      = "DpdDisConnector (getTrackingData) - Parcel Label Number: {$parcelLabelNumber} - response: {$lastResponse} - unknown Exception message: ";
            $message      .= $e->getMessage();

            throw new DpdTrackingResponseException($message);
        }

        if (! isset($result->trackingresult->statusInfo)) {
            $message = "DPD API -> getTrackingData doesn't contain any statusInfo!";

            throw new DpdTrackingResponseException($message);
        }

        return DpdParcelStatusInfo::fromDpdResponse($result->trackingresult->statusInfo);
    }
}
