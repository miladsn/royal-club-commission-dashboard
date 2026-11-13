<?php

namespace App\Services\Exchanges\Contracts;

interface ExcelImportableExchangeInterface
{
    public function parseExcel(string $filePath, array $options = []): array;
}
