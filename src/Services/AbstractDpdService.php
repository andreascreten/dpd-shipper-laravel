<?php

namespace Flooris\DpdShipper\Services;

use SoapFault;
use SoapClient;
use Flooris\DpdShipper\DpdShipperConnector;

abstract class AbstractDpdService
{
    private ?SoapClient $client = null;

    public function __construct(
        public DpdShipperConnector $connector
    )
    {

    }

    /**
     * @throws SoapFault
     */
    public function doSoapRequest(string $serviceName, string $serviceVersion, string $soapMethod, ?\SoapHeader $soapHeader, ?array $data, bool $trace = true, bool $exceptions = true): null|false|\StdClass
    {
        $serviceBaseUrl = $this->connector->baseUrl;
        $serviceUrl     = "{$serviceBaseUrl}{$serviceName}{$serviceVersion}.wsdl";
        $clientOptions  = [
            'trace'      => $trace,
            'exceptions' => $exceptions,
        ];

        $this->client = new SoapClient($serviceUrl, $clientOptions);

        if ($soapHeader) {
            $this->client->__setSoapHeaders($soapHeader);
        }

        return $this->client->$soapMethod($data);
    }

    public function getSoapLastResponse(): ?string
    {
        return $this->client?->__getLastResponse();
    }
}
