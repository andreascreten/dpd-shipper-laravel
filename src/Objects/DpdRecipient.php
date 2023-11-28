<?php

namespace Flooris\DpdShipper\Objects;

class DpdRecipient
{
    public function __construct(
        public readonly string $name1,
        public readonly string $street,
        public readonly string $houseNumber,
        public readonly string $countryIso,
        public readonly string $postalCode,
        public readonly string $city,
        public readonly string $email,
        public readonly string $type = 'P',
        public readonly string $name2 = '',
        public readonly string $street2 = '',
        public readonly string $phone = '',
    )
    {
    }

    private const COUNTRIES_NON_EU = [
        'CH',
    ];

    public function isNonEuCountry(): bool
    {
        return in_array($this->countryIso, self::COUNTRIES_NON_EU, true);
    }

    public function isPrivatePerson(): bool
    {
        return $this->type === 'P';
    }

    public function isBusiness(): bool
    {
        return $this->type === 'B';
    }

    public function toArray(): array
    {
        return [
            'name1'   => $this->name1,
            'name2'   => $this->name2,
            'street'  => $this->street,
            'street2' => $this->street2,
            'houseNo' => $this->houseNumber,
            'country' => $this->countryIso,
            'zipCode' => $this->postalCode,
            'city'    => $this->city,
            'email'   => $this->email,
            'phone'   => $this->phone,
            'type'    => $this->type,
        ];
    }
}
