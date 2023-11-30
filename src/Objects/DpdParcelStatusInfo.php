<?php

namespace Flooris\DpdShipper\Objects;

use Illuminate\Support\Collection;

class DpdParcelStatusInfo
{
    private Collection $statuses;

    public function __construct()
    {
        $this->statuses = collect();
    }

    public function addStatus(DpdParcelStatus $status): self
    {
        $this->statuses->push($status);

        return $this;
    }

    public function getCurrentStatus(): ?DpdParcelStatus
    {
        return $this->statuses->where('isCurrentStatus', true)->first();
    }

    public static function fromDpdResponse(array $array): self
    {
        $statusInfo = new self();

        foreach ($array as $status) {
            $statusInfo->addStatus(DpdParcelStatus::fromDpdResponse($status));
        }

        return $statusInfo;
    }

    public function isPickedUp(): bool
    {
        return $this->statuses->where('status', 'PickedUp')->isNotEmpty();
    }

    public function isDelivered(): bool
    {
        return $this->statuses->where('status', 'Delivered')->isNotEmpty();
    }
}
