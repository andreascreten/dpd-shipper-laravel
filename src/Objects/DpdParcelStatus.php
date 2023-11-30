<?php

namespace Flooris\DpdShipper\Objects;

use Illuminate\Support\Carbon;

class DpdParcelStatus
{
    public function __construct(
        public readonly string $status,
        public readonly string $description,
        public readonly Carbon $date,
        public readonly bool $statusHasBeenReached = false,
        public readonly bool $isCurrentStatus = false,
    )
    {
    }

    public static function fromDpdResponse(\stdClass $status): self
    {
        return new self(
            status: $status->status,
            description: $status->description->content->content,
            date: Carbon::parse($status->date->content),
            statusHasBeenReached: $status->statusHasBeenReached,
            isCurrentStatus: $status->isCurrentStatus,
        );
    }
}
