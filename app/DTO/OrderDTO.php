<?php

namespace App\DTO;

use Carbon\Carbon;

class OrderDTO
{
    public function __construct(
        protected ?Carbon $dateIssue,
        protected ?int $clientId,
        protected ?string $description,
        protected ?int $amountTotal,
        protected ?int $amountPayed,
        protected ?string $status,
        protected ?array $contents,
    ) {}

    public function getDateIssue(): string    {
        return $this->dateIssue->toDateTimeString();
    }

    public function setDateIssue(Carbon $dateIssue): void
    {
        $this->dateIssue = $dateIssue;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getAmountTotal(): ?int
    {
        return $this->amountTotal;
    }

    public function setAmountTotal(int $amountTotal): void
    {
        $this->amountTotal = $amountTotal;
    }

    public function getAmountPayed(): ?int
    {
        return $this->amountPayed;
    }

    public function setAmountPayed(int $amountPayed): void
    {
        $this->amountPayed = $amountPayed;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getContents(): ?array
    {
        return $this->contents;
    }

    public function setContents(array $contents): void
    {
        $this->contents = $contents;
    }
}
