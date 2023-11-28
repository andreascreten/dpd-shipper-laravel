<?php

namespace Flooris\DpdShipper\Objects;

class DpdSender
{
    public function __construct(
        public readonly string $name,
        public readonly string $street,
        public readonly string $houseNumber,
        public readonly string $countryIso,
        public readonly string $postalCode,
        public readonly string $city,
    )
    {

    }

    public function toArray(): array
    {
        return [
            'name1'   => $this->name,
            'street'  => $this->street,
            'houseNo' => $this->houseNumber,
            'country' => $this->countryIso,
            'zipCode' => $this->postalCode,
            'city'    => $this->city,
        ];
    }
}
