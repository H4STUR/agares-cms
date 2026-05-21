<?php

namespace App\Services\Ecommerce;

class ImportResult
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;
    /** @var array<int, array{row: int, message: string}> */
    public array $errors = [];

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function total(): int
    {
        return $this->created + $this->updated + $this->skipped;
    }
}
