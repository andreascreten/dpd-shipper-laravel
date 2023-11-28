<?php

namespace Flooris\DpdShipper\Objects;

class DpdParcels
{
    private string $customer_reference_number_1 = '';
    private string $customer_reference_number_2 = '';
    private int $weight = 0;
    private \DateTime $expiration_date;

    /**
     * @return string
     */
    public function getCustomerReferenceNumber1(): string
    {
        return $this->customer_reference_number_1;
    }

    /**
     * @param string $customer_reference_number_1
     */
    public function setCustomerReferenceNumber1(string $customer_reference_number_1): void
    {
        $this->customer_reference_number_1 = $customer_reference_number_1;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight(int $weight): void
    {
        $this->weight = $weight;
    }

    public function getExpirationDate(): \DateTime
    {
        return $this->expiration_date;
    }

    public function setExpirationDate(\DateTime $date): void
    {
        $this->expiration_date = $date;
    }

    /**
     * @return string
     */
    public function getCustomerReferenceNumber2(): string
    {
        return $this->customer_reference_number_2;
    }

    /**
     * @param string|null $customer_reference_number_2
     */
    public function setCustomerReferenceNumber2(string $customer_reference_number_2 = null): void
    {
        $this->customer_reference_number_2 = $customer_reference_number_2;
    }
}
