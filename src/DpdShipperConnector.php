<?php

namespace Flooris\DpdShipper;

use SoapHeader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Flooris\DpdShipper\Services\DpdLoginService;
use Flooris\DpdShipper\Services\DpdShipmentService;
use Flooris\DpdShipper\Services\DpdTrackingService;

class DpdShipperConnector
{
    private const CACHE_KEY_API_TOKEN = 'FLOORIS_DPD_API_TOKEN';

    public function __construct(
        public readonly string $id,
        public readonly string $password,
        public readonly string $depotNumber,
        public readonly string $baseUrl,
        public readonly string $authUrl,
        public readonly string $messageLanguage = 'en_US'
    )
    {
    }

    public function loginService(): DpdLoginService
    {
        return new DpdLoginService($this);
    }

    public function shipmentService(): DpdShipmentService
    {
        return new DpdShipmentService($this);
    }

    public function trackingService(): DpdTrackingService
    {
        return new DpdTrackingService($this);
    }

    public function getSoapAuthenticationHeader(): SOAPHeader
    {
        $soapHeaderBody = [
            'delisId'         => $this->id,
            'authToken'       => $this->loginService()->getApiToken(),
            'messageLanguage' => $this->messageLanguage,
        ];

        return new SOAPHeader($this->authUrl, 'authentication', $soapHeaderBody, false);
    }

    public function getApiTokenFromCache(): ?string
    {
        if (Cache::has(self::CACHE_KEY_API_TOKEN)) {
            return Cache::get(self::CACHE_KEY_API_TOKEN);
        }

        return null;
    }

    public function storeApiTokenInCache(string $token): bool
    {
        $expiresAt = Carbon::now()->addMinutes(120);

        return Cache::put(self::CACHE_KEY_API_TOKEN, $token, $expiresAt);
    }

    public function forgetApiTokenFromCache(): bool
    {
        return Cache::forget(self::CACHE_KEY_API_TOKEN);
    }
}
