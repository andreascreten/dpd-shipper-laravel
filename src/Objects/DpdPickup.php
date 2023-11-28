<?php

namespace Flooris\DpdShipper\Objects;

class DpdPickup
{
    public function __construct(
        public int $tour,
        public \DateTime $date,
        public DpdRecipient $address,
        public int $quantity = 1,
        public string $comment = '',
    )
    {
    }

    public function toArray(): array
    {
        return [
            'tour' => $this->tour,
            'quantity' => $this->quantity,
            'date' => $this->date->format('Ymd'),
            'day' => $this->date->format('N'),
            'collectionRequestAddress' => [
                ...$this->address->toArray(),
                'comment' => $this->comment,
            ],
        ];
    }
}
